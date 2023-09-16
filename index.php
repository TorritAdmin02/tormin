<?php
session_start();
if(isset($_SESSION['validated']))
{
    header("Location: /home");
}
else
{
    $captcha_id = substr(md5(time()), 0, 5);
    ?>

    <!DOCTYPE html>
    <html lang="en" dir="ltr">
<link rel="stylesheet" href="/public/style.css">

    <body>
    <div id="captcha">
        <?php
        if(isset($_SESSION['attempts']) && $_SESSION['attempts'] > 5)
        {
            echo '
        <text class="torrit-text">failed captcha too many times - get a new identity and try again</text>
        ';
            exit();
        }
        ?>
        <div>
            <img src='captcha/<?php echo $captcha_id; ?>'>
        </div>

        <div class="clearfix"></div>
        <form method="POST" action="auth">
            <input type="text" placeholder="Enter Captcha" name="user_input" style="width: 43%;line-height: 2;"/>
            <div class="clearfix"></div>
            <text class="torrit-text"><small><i>not* case sensitive</i></small></text>
            <div class="clearfix"></div>
            <input type="submit" class="torrit-button-small" value="Submit"/>
        </form>

    </div>
    </body>
    <div class="clearfix"></div>
    </html>

    <?php
    }
    ?>