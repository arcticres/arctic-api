<!DOCTYPE html>
<html>
<head>
    <title>Contact Us</title>
</head>
<body>
    <h1>Contact Us</h1>
    <form action="contact_form_process.php" method="post">
        <p><label>First Name: <input type="text" name="namefirst"></label></p>
        <p><label>Last Name: <input type="text" name="namelast"></label></p>
        <p><label>Email: <input type="text" name="email"></label></p>
        <p><label>Phone (Day): <input type="text" name="phone_day"></label></p>
        <p><label>Phone (Evening): <input type="text" name="phone_evening"></label></p>
        <p><label>Address: <input type="text" name="address"></label></p>
        <p><label>City: <input type="text" name="city"></label></p>
        <p><label>State: <input type="text" name="state"></label></p>
        <p><label>Zip: <input type="text" name="zip" size="6"></label></p>
        <p><label>Country: <input type="text" name="country" value="United States of America"></label></p>
        <p><label>Referral Source: <select name="hear">
                    <option value=""></option>
                    <option value="Internet Search">Internet Search</option>
                    <option value="Internet Ad">Internet Ad</option>
                    <option value="Magazine">Magazine</option>
                    <option value="Newspaper">Newspaper</option>
                    <option value="Radio">Radio</option>
                    <option value="Television">Television</option>
        </select></label></p>
        <p><label>Message:<br>
                <textarea name="message" rows="4" cols="36"></textarea></label></p>
        <p><button type="submit">Submit Request</button></p>
    </form>
</body>
</html>
