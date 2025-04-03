import requests
import pandas as pd
import sys
from advanced_ta import LorentzianClassification
from ta.volume import money_flow_index as MFI

def get_binance_ohlcv(symbol="BTCUSDT", interval="1h", limit=1000):
    url = "https://api.binance.com/api/v3/klines"
    params = {"symbol": symbol, "interval": interval, "limit": limit}
    print(f"Fetching {limit} candles for {symbol} ({interval})...")
    try:
        response = requests.get(url, params=params, timeout=10)
        response.raise_for_status()
        data = response.json()
        if not data:
            raise ValueError("Received empty data from Binance API.")

        df = pd.DataFrame(data, columns=['timestamp', 'open', 'high', 'low', 'close', 'volume',
                                         'close_time', 'quote_asset_volume', 'trades',
                                         'taker_base_vol', 'taker_quote_vol', 'ignore'])

        df['timestamp'] = pd.to_datetime(df['timestamp'], unit='ms')  # Convert timestamp
        df.set_index('timestamp', inplace=True)  # Set as index

        numeric_cols = ['open', 'high', 'low', 'close', 'volume']
        df[numeric_cols] = df[numeric_cols].apply(pd.to_numeric, errors='coerce')
        df.dropna(subset=numeric_cols, inplace=True)

        print(f"Fetched {len(df)} candles successfully.")
        return df[['open', 'high', 'low', 'close', 'volume']]

    except requests.exceptions.RequestException as e:
        raise Exception(f"Error fetching Binance data: {e}") from e

if __name__ == '__main__':
    try:
        # Get command-line arguments
        if len(sys.argv) != 5:
            print("Usage: python script.py SYMBOL INTERVAL LIMIT OUTPUT_PATH")
            sys.exit(1)

        symbol = sys.argv[1]     # Example: "BNBUSDT"
        interval = sys.argv[2]    # Example: "1m"
        limit = int(sys.argv[3])  # Example: 1000
        output_path = sys.argv[4] # Example: "output/result.csv"

        df = get_binance_ohlcv(symbol, interval, limit)

        lc = LorentzianClassification(
            df,
            features=[
                LorentzianClassification.Feature("RSI", 14, 2),  # f1
                LorentzianClassification.Feature("WT", 10, 11),  # f2
                LorentzianClassification.Feature("CCI", 20, 2),  # f3
                LorentzianClassification.Feature("ADX", 20, 2),  # f4
                LorentzianClassification.Feature("RSI", 9, 2),   # f5
                MFI(df['high'], df['low'], df['close'], df['volume'], window=14) # f6
            ],
            settings=LorentzianClassification.Settings(
                source=df['close'],
                neighborsCount=8,
                maxBarsBack=2000,
                useDynamicExits=False
            ),
            filterSettings=LorentzianClassification.FilterSettings(
                useVolatilityFilter=True,
                useRegimeFilter=True,
                useAdxFilter=False,
                regimeThreshold=-0.1,
                adxThreshold=20,
                kernelFilter=LorentzianClassification.KernelFilter(
                    useKernelSmoothing=False,
                    lookbackWindow=8,
                    relativeWeight=8.0,
                    regressionLevel=25,
                    crossoverLag=2,
                )
            )
        )

        lc.dump(output_path)
        print(f"Lorentzian Classification results saved to {output_path}")

    except Exception as e:
        print(f"An error occurred: {e}")
