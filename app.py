import os
from flask import Flask, request, jsonify
from transformers import pipeline

app = Flask(__name__)

MODEL_PATH = "./model"

print("⏳ Loading Anonymoustalk AI...")
if not os.path.exists(MODEL_PATH):
    print("❌ Error: Model folder not found!")
    exit()

classifier = pipeline("text-classification", model=MODEL_PATH, tokenizer=MODEL_PATH)
print("✅ AI Ready!")

@app.route('/analyze', methods=['POST'])
def analyze_text():
    data = request.json
    text = data.get('text', '')

    if not text:
        return jsonify({"error": "No text provided"}), 400

    result = classifier(text)[0]
    label = result['label']
    score = result['score']

    print("-" * 30)
    print(f"📥 Received Text: '{text}'")
    print(f"🔍 AI Prediction: {label}")
    print(f"📊 Confidence:    {score:.4f}")
    print("-" * 30)

    return jsonify({
        "status": "success",
        "result": label,
        "confidence": score
    })

if __name__ == '__main__':
    app.run(port=5000, debug=True)
