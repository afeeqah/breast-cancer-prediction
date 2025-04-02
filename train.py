import pandas as pd
from sklearn.preprocessing import MinMaxScaler, OneHotEncoder
from sklearn.compose import ColumnTransformer
from sklearn.ensemble import RandomForestClassifier
from sklearn.neural_network import MLPClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import classification_report, accuracy_score, precision_score, recall_score, f1_score
import pickle
import json
import numpy as np

def preprocess_and_train(data_path, new_data_path, model_path, scaler_path, features_path, preprocessed_data_path, selected_data_path):
    # Load data
    data = pd.read_csv(data_path)

    # Save original data
    data.to_csv('original_' + data_path, index=False)

    # Separate features and labels
    X = data.drop(columns=['diagnosis'])
    y = data['diagnosis']

    # Identify categorical columns
    categorical_columns = X.select_dtypes(include=['object']).columns.tolist()
    numeric_columns = X.select_dtypes(exclude=['object']).columns.tolist()

    # Preprocessor for numeric and categorical features
    transformers = [('num', MinMaxScaler(), numeric_columns)]
    if categorical_columns:
        transformers.append(('cat', OneHotEncoder(drop='first', sparse_output=False), categorical_columns))

    preprocessor = ColumnTransformer(transformers, remainder='passthrough')

    # Fit and transform the data
    X_preprocessed = preprocessor.fit_transform(X)

    # Save preprocessed data
    if categorical_columns:
        preprocessed_feature_names = numeric_columns + list(preprocessor.named_transformers_['cat'].get_feature_names_out(categorical_columns))
    else:
        preprocessed_feature_names = numeric_columns
    preprocessed_df = pd.DataFrame(X_preprocessed, columns=preprocessed_feature_names)
    preprocessed_df.to_csv(preprocessed_data_path, index=False)

    # Feature selection
    rf = RandomForestClassifier(n_estimators=100, random_state=42)
    rf.fit(X_preprocessed, y)
    feature_importances = rf.feature_importances_
    important_indices = feature_importances.argsort()[::-1][:10]

    # Select top 10 important features
    selected_features = [preprocessed_feature_names[idx] for idx in important_indices]

    # Map one-hot encoded features back to their original names
    def get_original_feature_name(encoded_feature):
        for cat_col in categorical_columns:
            if encoded_feature.startswith(cat_col + '_'):
                return cat_col
        return encoded_feature

    # Ensure the selected features are unique and maintain their original names without values appended
    original_selected_features = list(dict.fromkeys(get_original_feature_name(f) for f in selected_features))

    # Create a new preprocessor with only the selected features
    selected_numeric_columns = [col for col in numeric_columns if col in original_selected_features]
    selected_categorical_columns = [col for col in categorical_columns if col in original_selected_features]

    selected_transformers = []
    if selected_numeric_columns:
        selected_transformers.append(('num', MinMaxScaler(), selected_numeric_columns))
    if selected_categorical_columns:
        selected_transformers.append(('cat', OneHotEncoder(drop='first', sparse_output=False), selected_categorical_columns))

    selected_preprocessor = ColumnTransformer(selected_transformers, remainder='passthrough')

    # Fit and transform the data with the new preprocessor
    selected_columns = selected_numeric_columns + selected_categorical_columns
    X_selected_preprocessed = selected_preprocessor.fit_transform(X[selected_columns])

    # Save selected data
    selected_feature_names = selected_preprocessor.get_feature_names_out()
    selected_df = pd.DataFrame(X_selected_preprocessed, columns=selected_feature_names)
    selected_df.to_csv(selected_data_path, index=False)

    # Save the new preprocessor
    with open(scaler_path, 'wb') as scaler_file:
        pickle.dump(selected_preprocessor, scaler_file)

    # Print selected features
    print("Selected features:")
    for i, feature in enumerate(original_selected_features, 1):
        print(f"{i}. {feature}")

    # Save the selected features to a JSON file
    with open(features_path, 'w') as features_file:
        json.dump(original_selected_features, features_file)

    # Prepare new data
    new_data = pd.DataFrame(X_selected_preprocessed, columns=selected_feature_names)
    new_data['diagnosis'] = y.values
    new_data.to_csv(new_data_path, index=False)

    # Generate a new random state for each run
    random_state = np.random.randint(0, 10000)

    # Split data
    X_train, X_test, y_train, y_test = train_test_split(X_selected_preprocessed, y, test_size=0.2, random_state=random_state)

    # Train model
    mlp = MLPClassifier(hidden_layer_sizes=(100,), max_iter=500, random_state=random_state)
    mlp.fit(X_train, y_train)

    # Evaluate model
    y_pred = mlp.predict(X_test)
    report = classification_report(y_test, y_pred)
    print(report)

    accuracy = accuracy_score(y_test, y_pred) * 100
    precision = precision_score(y_test, y_pred, average='weighted') * 100
    recall = recall_score(y_test, y_pred, average='weighted') * 100
    f1 = f1_score(y_test, y_pred, average='weighted') * 100

    print(f"1) Accuracy: {accuracy:.2f}%")
    print(f"2) Precision: {precision:.2f}%")
    print(f"3) Recall: {recall:.2f}%")
    print(f"4) F1-Score: {f1:.2f}%")

    # Save metrics to JSON file
    metrics = {
        'accuracy': accuracy,
        'precision': precision,
        'recall': recall,
        'f1': f1
    }
    with open(model_path.replace('.pkl', '_metrics.json'), 'w') as json_file:
        json.dump(metrics, json_file)

    # Save model
    with open(model_path, 'wb') as model_file:
        pickle.dump(mlp, model_file)

# Paths to save models, scalers, and features
paths_with_values = [
    ("data1.csv", "new_data1.csv", "model1.pkl", "preprocessor1.pkl", "selected_features1.json", "preprocessed_data1.csv", "selected_data1.csv"),
    ("data2.csv", "new_data2.csv", "model2.pkl", "preprocessor2.pkl", "selected_features2.json", "preprocessed_data2.csv", "selected_data2.csv")
]

# Train and save models for datasets
for data_path, new_data_path, model_path, scaler_path, features_path, preprocessed_data_path, selected_data_path in paths_with_values:
    preprocess_and_train(data_path, new_data_path, model_path, scaler_path, features_path, preprocessed_data_path, selected_data_path)