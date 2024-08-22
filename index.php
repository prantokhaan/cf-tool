<!DOCTYPE html>  
<html lang="en">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <meta name="description" content="A platform to view and track Codeforces problems, including filtering, searching, and solving problems with tracking features.">  
    <meta name="keywords" content="Codeforces, programming, problems, tracking, contests">  
    <meta name="author" content="PRANTO">  
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">  
    <title>Codeforces Problems</title>  
    <link rel="stylesheet" href="./css/nav.css">  
    <style>  
        body {  
            font-family: 'Poppins', sans-serif;  
            margin: 20px;  
            background-color: #f9f9f9;  
            color: #333;  
        }  

        h1 {  
            font-weight: 600;  
            color: #3498db;  
            text-align: center;  
            margin-bottom: 20px;  
        }  

        p {  
            color: #555;  
            font-size: 1.1em;  
            line-height: 1.5;  
        }  

        ul {  
            list-style-type: none; /* Remove default bullets */  
            padding: 0;  
            max-width: 600px;  
            margin: 0 auto; /* Center the list */  
        }  

        li {  
            position: relative;  
            margin-bottom: 15px;  
            padding-left: 25px; /* Space for bullets */  
        }  

        li:before {  
            content: '•'; /* Custom bullet */  
            color: #3498db; /* Bullet color */  
            position: absolute;   
            left: 0;  
            font-size: 1.5em; /* Bullet size */  
            line-height: 0.8; /* Bullet line height */  
        }  

        footer {  
            text-align: center;  
            margin-top: 30px;  
            font-size: 0.9em;  
            color: #777;  
        }   

        /* Add some responsive design */  
        @media (max-width: 600px) {  
            h1 {  
                font-size: 1.8em;  
            }  

            p, li {  
                font-size: 1em;  
            }  
        }  

        /* Style for bold texts */  
        .bold {  
            font-weight: bold;  
            margin-top: 20px;  
        }  
    </style>  
</head>  
<body>  
    <?php include './shared/nav.php'; ?>  
    <h1>Welcome to Codeforces Problems</h1>  
    <p>This platform offers the following features:</p>  
    <ul>  
        <li>This platform will fetch Codeforces problems, allowing you to filter and search for problems.</li>  
        <li>Clicking on a random problem will provide you with a problem based on your filters.</li>  
        <li>When you start solving a problem, you can start a timer, and once you solve it, you can save your progress for tracking purposes.</li>  
        <li>The solve tracking page will display a list of problems solved, categorized by rating.</li>  
        <li>Statistics on the number of problems solved in each topic will be available.</li>  
        <li>You will also find statistical insights like time taken and average time.</li>  
        <li>The profile page will showcase basic details from Codeforces, including the number of problems solved by rating and index, along with a contest rating graph.</li>  
    </ul>  
    <p class="bold">For a better experience, we kindly request you to register before using the platform. <a href="http://cp.ismatulislampranto.com/auth/register.php">Click here to register/login</a>.</p>  
    <p class="bold">If you encounter any bugs or issues while using the platform, please do not hesitate to email us at <a href="mailto:cp@ismatulislampranto.com">cp@ismatulislampranto.com</a>.</p>  
    <footer>  
        <p>&copy; Built by a noob guy named PRANTO</p>  
    </footer>  
</body>  

<!-- <script src="./js/navbar.js"></script> -->  
</html>