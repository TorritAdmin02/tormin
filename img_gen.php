<?php
//STARTING  SESSION
  //GENERATING RANDOM 4 CHARACTER FOR CAPTCHA
    if(isset($_GET['cid'])) {
        $captcha_id = $_GET['cid'];
        if (strlen($captcha_id) == 5) {


            $string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefhijklmnopqrstuvwxyz1234567890';
            $string_shuff = str_shuffle($string);
            $text = substr($string_shuff, 0, 5);

            //STARTING AND CREATING SESSION
            session_start();
            $_SESSION['secure'] = strtolower($text);
//DEFINING CONTENT TYPE TO IMAGE - JPEG
            header('content-type: image/jpeg');
//GETTING SESSION VARIABLE
            $text = $_SESSION['secure'];

//CREATING IMAGE WITH DIMENTION 158x60
            $image_height = 200;
            $image_width = 275;
            $image = imagecreate($image_width, $image_height);
            //DEFINING BACKGROUND COLOUR TO WHITE
            imagecolorallocate($image, 255, 255, 255);


//FOR LOOP FOR CREATING TEXT
            for ($i = 1; $i <= 5; $i++) {
                //CREATING RANDOM FONT-SIZE
                $font_size = rand(32, 37);
                //FOR RANDOM COLOUR
                $r = rand(0, 255);
                $g = rand(0, 255);
                $b = rand(0, 255);
                //RANDOM INDEX FOR RANDOM TEXT FONT
                $index = rand(1, 10);
                //RANDOM POSITION AND ORIANTION
                $x = 45 + (50 * ($i - 1));
                $x = rand($x - 15, $x + 15);
                $y = rand(105, 125);
                $o = rand(-50, 50);
                //RANDOM FONT COLOR
                $font_color = imagecolorallocate($image, $r, $g, $b);
                //CREATING IMAGE USING DIFFETENT FONTS
                imagettftext($image, $font_size, $o, $x, $y, $font_color, 'fonts/' . $index . '.ttf', $text[$i - 1]);
            }
//FOR LOOP FOR CREATING RANDOM LINES
            for ($i = 1; $i <= 25; $i++) {
                //RANDOM STARTING AND ENDING POSITION
                $x1 = rand(15, 250);
                $y1 = rand(1, 250);
                $x2 = rand(15, 250);
                $y2 = rand(1, 250);
                //RANDOM COLOR
                $r = rand(0, 255);
                $g = rand(0, 255);
                $b = rand(0, 255);
                $font_color = imagecolorallocate($image, $r, $g, $b);
                //CREATING RANDOM LINES
                imageline($image, $x1, $y1, $x2, $y2, $font_color);


            }
//CREATING FINAL IMAGE (CAPTCHA)
            imagejpeg($image);
        }
    }


?>
