from flask import Flask, request, jsonify
from flask_cors import CORS
import pandas as pd
import json
import pickle
import os
from sklearn.preprocessing import MinMaxScaler, LabelEncoder
from sklearn.compose import ColumnTransformer
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score, precision_score, recall_score, f1_score
from sklearn.neural_network import MLPClassifier

app = Flask(__name__)
CORS(app)

# Load models, preprocessors, and selected features
models = {}
preprocessors = {}
selected_features = {}
metrics = {}

def load_assets(dataset_key, model_path, preprocessor_path, features_path, metrics_path):
    if os.path.exists(model_path):
        models[dataset_key] = pickle.load(open(model_path, 'rb'))
    if os.path.exists(preprocessor_path):
        preprocessors[dataset_key] = pickle.load(open(preprocessor_path, 'rb'))
    if os.path.exists(features_path):
        with open(features_path, 'r') as f:
            selected_features[dataset_key] = json.load(f)
    if os.path.exists(metrics_path):
        with open(metrics_path, 'r') as f:
            metrics[dataset_key] = json.load(f)

# Load assets for predefined datasets
load_assets('dataset1', 'model1.pkl', 'preprocessor1.pkl', 'selected_features1.json', 'model1_metrics.json')
load_assets('dataset2', 'model2.pkl', 'preprocessor2.pkl', 'selected_features2.json', 'model2_metrics.json')

# Load assets for uploaded dataset
load_assets('uploaded', 'uploads/uploaded_model.pkl', 'uploads/uploaded_preprocessor.pkl', 'uploads/uploaded_features.json', 'uploads/uploaded_metrics.json')

def remove_nan_values(data):
    return json.loads(json.dumps(data, default=lambda x: None))

@app.route('/upload', methods=['POST'])
def upload_dataset():
    try:
        file = request.files['dataset']
        target_feature = request.form['target']
        dataset_path = os.path.join('uploads', file.filename)
        file.save(dataset_path)

        data = pd.read_csv(dataset_path)

        # Remove rows with NaN values
        data.dropna(inplace=True)

        # Check if the target feature exists
        if target_feature not in data.columns:
            return jsonify({'error': 'Target feature not found in the dataset.'}), 400

        return jsonify({'columns': data.columns.tolist(), 'preview': data.to_dict(orient='records'), 'path': dataset_path}), 200
    except Exception as e:
        print(f"Error in upload: {e}")
        return jsonify({'error': str(e)}), 500

@app.route('/preprocess', methods=['POST'])
def preprocess():
    try:
        request_data = request.get_json()
        dataset_path = request_data.get('dataset_path')
        target_feature = request_data.get('target')

        if not dataset_path or not target_feature:
            return jsonify({'error': 'Invalid data'}), 400

        df = pd.read_csv(dataset_path)

        if target_feature not in df.columns:
            return jsonify({'error': 'Target feature not found in the dataset.'}), 400

        # Remove rows with NaN values
        df.dropna(inplace=True)

        # Separate features and labels
        X = df.drop(columns=[target_feature])
        y = df[target_feature]

        # Identify categorical columns and string columns
        categorical_columns = X.select_dtypes(include=['object']).columns.tolist()
        numeric_columns = X.select_dtypes(exclude=['object']).columns.tolist()

        # Identify and remove string columns
        string_columns = [col for col in categorical_columns if X[col].nunique() > 5]
        X = X.drop(columns=string_columns)
        categorical_columns = [col for col in categorical_columns if col not in string_columns]

        # Encode categorical columns with 5 or fewer unique values
        for col in categorical_columns:
            if X[col].nunique() <= 5:
                le = LabelEncoder()
                X[col] = le.fit_transform(X[col])
                # Scale to 0-1 range
                X[col] = X[col] / (X[col].max() if X[col].max() != 0 else 1)

        # Preprocessor for numeric features
        transformers = [('num', MinMaxScaler(), numeric_columns)]
        preprocessor = ColumnTransformer(transformers, remainder='passthrough')

        # Fit and transform the data
        X_preprocessed = preprocessor.fit_transform(X)

        # Save preprocessed data
        preprocessed_feature_names = numeric_columns + categorical_columns
        preprocessed_df = pd.DataFrame(X_preprocessed, columns=preprocessed_feature_names)
        preprocessed_df[target_feature] = y.values  # Add target feature back to the preprocessed data

        # Save the preprocessor for later use in training
        preprocessor_path = 'uploads/uploaded_preprocessor.pkl'
        with open(preprocessor_path, 'wb') as preprocessor_file:
            pickle.dump(preprocessor, preprocessor_file)

        return jsonify({
            'preprocessed_data': remove_nan_values(preprocessed_df.to_dict(orient='records')),
            'preprocessed_columns': preprocessed_feature_names + [target_feature],
            'preprocessor_path': preprocessor_path
        }), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/select_features', methods=['POST'])
def select_features():
    try:
        request_data = request.get_json()
        preprocessed_data = request_data.get('data')
        target_feature = request_data.get('target')

        df = pd.DataFrame(preprocessed_data)
        print(f"Selecting features from dataset with columns: {df.columns.tolist()}")

        # Ensure target feature is present
        if target_feature not in df.columns:
            return jsonify({'error': f"Target feature {target_feature} not found in dataset columns"}), 400

        # Separate features and labels
        X = df.drop(columns=[target_feature])
        y = df[target_feature]

        # Feature selection
        rf = RandomForestClassifier(n_estimators=100, random_state=42)
        rf.fit(X, y)
        feature_importances = rf.feature_importances_
        important_indices = feature_importances.argsort()[::-1][:10]

        # Select top 10 important features
        selected_features = [X.columns[idx] for idx in important_indices]

        return jsonify({'selected_features': selected_features}), 200
    except Exception as e:
        print(f"Error in feature selection: {e}")
        return jsonify({'error': str(e)}), 500

@app.route('/train', methods=['POST'])
def train_model():
    try:
        data = request.json['data']
        target_feature = request.json['target']
        selected_features = request.json['selected_features']
        preprocessor_path = request.json['preprocessor_path']

        df = pd.DataFrame(data)
        print(f"Training model on dataset with columns: {df.columns.tolist()}")

        # Separate features and labels
        X = df[selected_features]
        y = df[target_feature]

        # Load the preprocessor
        with open(preprocessor_path, 'rb') as preprocessor_file:
            preprocessor = pickle.load(preprocessor_file)

        # Split data
        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

        # Train model
        mlp = MLPClassifier(hidden_layer_sizes=(100,), max_iter=500, random_state=42)
        mlp.fit(X_train, y_train)

        # Evaluate model
        y_pred = mlp.predict(X_test)
        accuracy = accuracy_score(y_test, y_pred) * 100
        precision = precision_score(y_test, y_pred, average='weighted') * 100
        recall = recall_score(y_test, y_pred, average='weighted') * 100
        f1 = f1_score(y_test, y_pred, average='weighted') * 100

        metrics = {
            'accuracy': accuracy,
            'precision': precision,
            'recall': recall,
            'f1': f1
        }

        # Save model and metrics
        model_path = 'uploads/uploaded_model.pkl'
        features_path = 'uploads/uploaded_features.json'
        metrics_path = 'uploads/uploaded_metrics.json'

        with open(model_path, 'wb') as model_file:
            pickle.dump(mlp, model_file)
        with open(features_path, 'w') as features_file:
            json.dump(selected_features, features_file)
        with open(metrics_path, 'w') as metrics_file:
            json.dump(metrics, metrics_file)

        return jsonify({'model_path': model_path, 'metrics': remove_nan_values(metrics)}), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/predict', methods=['POST'])
def predict():
    try:
        data = request.json
        input_data = data['input']
        dataset_key = data['dataset']

        if dataset_key not in models:
            return jsonify({'error': 'Invalid dataset key or model not available'}), 400

        model = models[dataset_key]
        preprocessor = preprocessors[dataset_key]
        features = selected_features[dataset_key]

        # Transform the input data using the preprocessor
        df = pd.DataFrame([input_data], columns=features)
        X = preprocessor.transform(df)

        # Make predictions
        prediction = model.predict(X)

        # Retrieve metrics for the model
        model_metrics = metrics[dataset_key]

        response = {
            'predictions': prediction.tolist(),
            'metrics': remove_nan_values(model_metrics)
        }

        return jsonify(response), 200
    except Exception as e:
        print(f"Error in predict: {e}")
        return jsonify({'error': str(e)}), 500

@app.route('/predict/uploaded', methods=['POST'])
def predict_uploaded():
    try:
        input_data = request.json['input']

        if 'uploaded' not in models:
            return jsonify({'error': 'Uploaded model not available'}), 400

        model = models['uploaded']
        preprocessor = preprocessors['uploaded']
        features = selected_features['uploaded']

        # Transform the input data using the preprocessor
        df = pd.DataFrame([input_data], columns=features)
        X = preprocessor.transform(df)

        # Make predictions
        prediction = model.predict(X)

        return jsonify({'predictions': prediction.tolist()}), 200
    except Exception as e:
        print(f"Error in predict: {e}")
        return jsonify({'error': str(e)}), 500

@app.route('/upload_train', methods=['POST'])
def upload_train():
    try:
        data = request.json['data']
        target_feature = request.json['target']
        selected_features = request.json['selected_features']
        preprocessor_path = request.json['preprocessor_path']

        df = pd.DataFrame(data)
        print(f"Training model on uploaded dataset with columns: {df.columns.tolist()}")

        # Separate features and labels
        X = df[selected_features]
        y = df[target_feature]

        # Load the preprocessor
        with open(preprocessor_path, 'rb') as preprocessor_file:
            preprocessor = pickle.load(preprocessor_file)

        # Split data
        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

        # Train model
        mlp = MLPClassifier(hidden_layer_sizes=(100,), max_iter=500, random_state=42)
        mlp.fit(X_train, y_train)

        # Evaluate model
        y_pred = mlp.predict(X_test)
        accuracy = accuracy_score(y_test, y_pred) * 100
        precision = precision_score(y_test, y_pred, average='weighted') * 100
        recall = recall_score(y_test, y_pred, average='weighted') * 100
        f1 = f1_score(y_test, y_pred, average='weighted') * 100

        metrics = {
            'accuracy': accuracy,
            'precision': precision,
            'recall': recall,
            'f1': f1
        }

        # Save model and metrics
        model_path = 'uploads/uploaded_model.pkl'
        features_path = 'uploads/uploaded_features.json'
        metrics_path = 'uploads/uploaded_metrics.json'

        with open(model_path, 'wb') as model_file:
            pickle.dump(mlp, model_file)
        with open(features_path, 'w') as features_file:
            json.dump(selected_features, features_file)
        with open(metrics_path, 'w') as metrics_file:
            json.dump(metrics, metrics_file)

        return jsonify({'model_path': model_path, 'metrics': remove_nan_values(metrics)}), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/publish_form', methods=['POST'])
def publish_form():
    try:
        form_id = request.json['form_id']
        # Logic to update form status to published in the database
        # This would typically involve a SQL UPDATE statement
        return jsonify({'status': 'success'}), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    app.run(debug=True)


