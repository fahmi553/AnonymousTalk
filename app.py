import os
from flask import Flask, request, jsonify
from flask_cors import CORS
from transformers import pipeline

app = Flask(__name__)
CORS(app)

MODEL_PATH = "./model"

print("⏳ Loading Anonymoustalk AI (DistilRoBERTa)...")
if not os.path.exists(MODEL_PATH):
    print(f"❌ Error: Model folder not found at {MODEL_PATH}!")
    exit(1)

try:
    classifier = pipeline("text-classification", model=MODEL_PATH, tokenizer=MODEL_PATH)
    print("✅ AI Ready!")
except Exception as e:
    print(f"❌ Failed to load model: {e}")
    exit(1)

@app.route('/analyze', methods=['POST'])
def analyze_text():
    data = request.get_json()
    if not data:
        return jsonify({"status": "error", "message": "No JSON data received"}), 400

    text = data.get('text', '')

    if not text:
        return jsonify({"status": "error", "message": "No text provided"}), 400

    try:
        result = classifier(text)[0]
        label = result['label']
        score = result['score']
        print("-" * 30)
        print(f"📥 Received: '{text[:50]}...'")
        print(f"🔍 Prediction: {label} ({score:.4f})")
        print("-" * 30)

        return jsonify({
            "status": "success",
            "result": label,
            "confidence": score
        })
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500

if __name__ == '__main__':
    port = int(os.environ.get("PORT", 10000))
    app.run(host='0.0.0.0', port=port)
