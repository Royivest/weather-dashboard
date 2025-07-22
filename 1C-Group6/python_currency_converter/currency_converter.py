"""
Currency conversion API
This file provides a simple REST API for currency conversion
Supports conversion between JPY (Japanese Yen), EUR (Euro), and HKD (Hong Kong Dollar)
"""

from flask import Flask, request, jsonify
from flask_cors import CORS

# Create Flask application instance
app = Flask(__name__)
# Enable Cross-Origin Resource Sharing (CORS)
CORS(app)

# Example static rates for demonstration
RATES = {
    ('USD', 'HKD'): 7.8,
    ('HKD', 'USD'): 1/7.8,
    ('USD', 'EUR'): 0.92,
    ('EUR', 'USD'): 1/0.92,
    ('USD', 'JPY'): 155.0,
    ('JPY', 'USD'): 1/155.0,
    ('HKD', 'EUR'): 0.92/7.8,
    ('EUR', 'HKD'): 7.8/0.92,
    ('HKD', 'JPY'): 155.0/7.8,
    ('JPY', 'HKD'): 7.8/155.0,
    ('EUR', 'JPY'): 155.0/0.92,
    ('JPY', 'EUR'): 0.92/155.0,
}

@app.route('/convert')
def convert():
    from_currency = request.args.get('from')
    to_currency = request.args.get('to')
    amount = request.args.get('amount', type=float)
    if not from_currency or not to_currency or amount is None:
        return jsonify({'error': 'Missing parameters'}), 400
    key = (from_currency.upper(), to_currency.upper())
    rate = RATES.get(key)
    if rate is None:
        return jsonify({'error': 'Conversion rate not found'}), 400
    result = amount * rate
    return jsonify({'result': round(result, 2)})

# If running this file directly, start the Flask server
if __name__ == '__main__':
    # Listen on all network interfaces, use port 5000
    app.run(host='0.0.0.0', port=5000)
