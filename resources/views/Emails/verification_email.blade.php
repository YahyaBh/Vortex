<style>
    @font-face {
        font-family: Louis;
        src: url(../../../public/Louis\ George\ Cafe\ Light.ttf);
    }

    
    * {
        font-family: Louis;
    }

    .main-container {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        background-color: #fff;
        padding: 50px;
        width: 50%;
        height: 400px;
        margin: auto;
        border-radius: 10px;
        box-shadow: 0 0 10px -2px grey;
    }

    .main-container img {
        width: 80px;
    }

    .button-ver {
        background-color: #ffe662;
        color: #000;
        text-decoration: none;
        width: auto;
        height: 50px;
        border-radius: 25px;
        border: none;
        border-bottom: 3px solid #2c2827;
        font-size: auto;
        font-weight: bold;
        margin-top: 20px;
        transition: all 0.3s ease;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 10px 20px;
    }
    .button-ver:hover {
        background-color: #2c2827;
        color: #fff;
        width: auto;
        border-radius: 25px;
        border: none;
        border-bottom: 3px solid #ffe662;
        font-size: 20px;
        font-weight: bold;
        transform: scale(0.95);
    }

    .footer {
        color: grey;
    }

    .webina {
        color: #ffe662;
    }

    footer {
        background-color: #dddddd42;
        width: auto;
        padding: 10px;
        border-radius: 10px;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }

    footer a {
        background-color: none;
        padding: none;
        width: auto;
        color: gray;
    }
</style>

<div class="main-container">
    <img src="../../../public/Images/WEBINA2.png" alt="logo" />
    <h3 style="text-align: left">
        Hello {{ $name }}, Verify your email address to complete registration
    </h3>

    <p>
        Thanks for your interest in joining WebIna! To complete your
        registration, we need you to verify your email address.
    </p>

    <a class="button-ver" href={{ $url }}> Verify Email </a>

    <p class="footer">
        Please activate this email within 24 hours.
        <br />
        Thanks for your time,
        <br />
        The <span class="webina"> WebIna </span> Team.
    </p>

    <footer>
        <div><a href="https://web-ina.com/privacy&policy">Privacy Policy</a> | <a href="https://web-ina.com/contact">Contact Support</a></div>
        <br />
        <p>© 2022 WebIna LTD.</p>
    </footer>
</div>
