import os
import sys

print("🚀 Waking up Python to install your new packages...")
# This forces python to install both apify-client and pandas internally
os.system(f'"{sys.executable}" -m pip install apify-client pandas')
print("✨ Installation attempt complete!")