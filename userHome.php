<?php 
include('userNavbar.php'); 
session_start(); // Start the session to access session variables
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Homepage</title>
    <link rel="stylesheet" href="css/userHome.css">
</head>
<body>

    <div class="container">
        <?php
        // Check if the username session variable is set
        if(isset($_SESSION['user_username'])) {
            // Retrieve the username from the session
            $username = $_SESSION['user_username'];

            // Display the welcome message with the username
            echo "<h1 class='welcome-message'>Welcome, $username!</h1>";
        }?>
        <p class="intro">The Breast Cancer Prediction System is a powerful tool designed to assist in the early detection and prediction of breast cancer. By analyzing various factors and utilizing advanced machine learning algorithms, the system aims to provide accurate predictions and valuable insights to users.</p>

        <div class="main-grid">
            <section class="section-feature">
                <h2>Feature Highlights</h2>

                <div class="sub-container">
                    <h3>Dataset Preprocessing</h3>
                    <p><strong>Loading the Data:</strong> The dataset is loaded from a CSV file which contains various features related to breast cancer.</p>
                    <p><strong>Handling Missing Values:</strong> Any missing values in the dataset are either filled with the mean/median of the column or dropped if necessary.</p>
                    <p><strong>Encoding Categorical Variables:</strong> Categorical variables are encoded into numerical values using techniques like one-hot encoding or label encoding.</p>
                    <p><strong>Normalization:</strong> The features are scaled to a standard range, typically between 0 and 1, using MinMaxScaler. This ensures that all features contribute equally to the training process.</p>
                    <div class="feature-images">
                        <img src="uploads/minmaxscaler.png" alt="MinMaxScaler" class="feature-image">
                    </div>
                </div>

                <div class="sub-container">
                    <h3>Feature Selection</h3>
                    <p><strong>Feature Importance:</strong> A RandomForestClassifier is used to determine the importance of each feature in the dataset. This helps in identifying which features are most relevant for predicting the target variable.</p>
                    <p><strong>Selecting Top Features:</strong> Based on the feature importance scores, the top features are selected. This reduces the dimensionality of the dataset and helps in improving the model's performance and training time.</p>
                    <div class="feature-images">
                        <img src="uploads/randomforest.png" alt="Random Forest" class="feature-image">
                    </div>
                </div>

                <div class="sub-container">
                    <h3>Training the MLP (Multilayer Perceptron)</h3>
                    <p><strong>Data Splitting:</strong> The dataset is split into training and testing sets. Typically, 80% of the data is used for training and 20% for testing.</p>
                    <p><strong>Model Architecture:</strong> An MLP model is constructed with an input layer (equal to the number of selected features), one or more hidden layers, and an output layer.</p>
                    <p><strong>Model Compilation:</strong> The model is compiled with a loss function (e.g., binary cross-entropy for binary classification) and an optimizer (e.g., Adam).</p>
                    <p><strong>Training the Model:</strong> The model is trained on the training data for a specified number of epochs. During training, the model learns to map the input features to the target variable.</p>
                    <p><strong>Evaluation:</strong> The trained model is evaluated on the testing set to determine its performance. Metrics such as accuracy, precision, recall, and F1-score are computed to assess the model's effectiveness.</p>
                    <div class="feature-images">
                        <img src="uploads/mlp.png" alt="Multilayer Perceptron" class="feature-image">
                    </div>
                </div>

            </section>

            <section class="section-privacy">
                <h2>Data Privacy and Security</h2>
                <p>We prioritize the privacy and security of your data. Measures such as encryption, access controls, and regular audits are implemented to safeguard your information and maintain confidentiality.</p>
            </section>

            <section class="section-faq">
                <h2>FAQs</h2>
                <p><strong>1. How accurate is the prediction system?</strong></p>
                <p>The system is designed to provide high accuracy in predictions. However, the accuracy can vary based on the quality and quantity of the input data.</p>
                <p><strong>2. Is my personal data secure?</strong></p>
                <p>Yes, we use advanced encryption and access control measures to ensure your data is secure.</p>
                <p><strong>3. How can I interpret the prediction results?</strong></p>
                <p>The prediction results provide a probability score indicating the likelihood of breast cancer. Detailed reports and visualizations are available to help you understand the results.</p>
            </section>

            <section class="section-action">
                <h2>Call to Action</h2>
                <p>Take action now by accessing the prediction tool and empowering yourself with valuable insights about breast cancer risk. Share this platform with others who may benefit from it.</p>
            </section>

            <section class="section-resource">
                <h2>Links to Resources</h2>
                <ul>
                    <li><a href="https://www.breastcancer.org.my/">Breast Cancer Welfare Association Malaysia</a></li>
                    <li><a href="https://www.breastcancerfoundation.org.my/">Breast Cancer Foundation Malaysia</a></li>
                    <li><a href="https://www.cancer.org.my/">National Cancer Society Malaysia</a></li>
                    <li><a href="https://www.thrive-malaysia.com/">Thrive Malaysia</a></li>
                    <li><a href="https://www.cancerresearch.my/">Cancer Research Malaysia</a></li>
                </ul>
            </section>

            <section class="section-contact">
                <h2>Contact Information</h2>
                <p><strong>Breast Cancer Foundation Malaysia (BCFM)</strong></p>
                <p>Phone: +603 7349 7200</p>
                <p>Location: C-29-1 The Troika, Persiaran KLCC, 50450 Kuala Lumpur, Malaysia.</p>
                <p>Website: <a href="https://www.breastcancerfoundation.org.my/">Breast Cancer Foundation Malaysia</a></p>

                <p><strong>Breast Cancer Welfare Association Malaysia (BCWA)</strong></p>
                <p>Phone: +603 7954 0133 or +6014 353 2042</p>
                <p>Location: 5th Floor, Bangunan Sultan Salahuddin Abdul Aziz Shah, 16 Jalan Utara, 46200 Petaling Jaya, Selangor, Malaysia.</p>
                <p>Website: <a href="https://www.breastcancer.org.my/contact.html">BCWA</a></p>

                <p><strong>National Cancer Society of Malaysia (NCSM)</strong></p>
                <p>Phone: 1-800-800-1000</p>
                <p>Email: help@cancer.org.my</p>
                <p>Provides support for advanced breast cancer patients.</p>
                <p>Website: <a href="https://cancer.org.my/get-help/advanced-breast-cancer/">NCSM - Advanced Breast Cancer</a></p>
            </section>
        </div>
    </div>
</body>
</html>
