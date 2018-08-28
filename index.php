<?php
error_reporting(E_ERROR);

/**
 * Replace last occurence of a substring in string.
 *
 * @param $search
 * @param $replace
 * @param $subject
 * @return mixed
 */
function str_lreplace($search, $replace, $subject) {
    $pos = strrpos($subject, $search);
    if ($pos !== false) {
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }
    return $subject;
}

$string = file_get_contents("./config.json");
$config = json_decode($string, true);
$variables = $config['variables'];
$form = $config['form'];
$questions = $config['questions'];

if(isset($_GET['r'])) {
    unset($_POST);
}

if (isset($_POST)) {
    $gender = $_POST['gender'];
    switch ($gender) {
        case 'male':
            $heshe = 'he';
            $himher = 'him';
            $self = 'himself';
            $hisher = 'his';
            break;
        case 'female':
            $heshe = 'she';
            $himher = 'her';
            $self = 'herself';
            $hisher = 'her';
            break;
        case 'transgender male to female':
            break;
        case 'transgender female to male':
            break;
    }

    $arr_questions = [];
    $arr_answers = [];
    foreach ($questions as $heading => $qs) {
        foreach ($qs as $q) {
            $arr_questions[] = $q;
        }
    }

    foreach ($_POST as $key => $value) {
        if (stripos($key, 'qs_') !== false) {
            $qs_index = str_replace('qs_', '', $key);
            if ($qs_index >= 0) {
                $the_qs = $arr_questions[$qs_index];
                $response = $the_qs['responses'][array_rand($the_qs['responses'])]; // pick a random response

                if (is_array($value)) {

                    $value = array_map(function($v) use ($qs_index) {
                        if($v == 'textfield') {
                            $v = $_POST['textfield_'.$qs_index];
                        }
                        return $v;
                    }, $value);

                    $str_value = implode(", ", $value);
                    $value = str_lreplace(", ", " and ", $str_value);
                    $result = str_replace('[response list]', $value, $response) . '<br>';
                } else {
                    if($value == 'textfield') {
                        $value = $_POST['textfield_'.$qs_index];
                    }
                    $result = str_replace('[response]', $value, $response);
                }

                $result = str_replace('[heshe]', $heshe, $result);
                $result = str_replace('[self]', $self, $result);
                $result = str_replace('[himher]', $himher, $result);
                $result = str_replace('[hisher]', $hisher, $result);

                $arr_answers[] = ucfirst($result);
            }
        }
    }


}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title><?= $variables['form title'] ?></title>
</head>
<body>

<div class="container" style="margin:30px;">

    <?php
    if(!isset($_POST)) {
        ?>
        <h3><?= $variables['form title'] ?></h3>

        <form method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
            <?php
            foreach ($form as $key => $value) {
                ?>
                <h4><?= $key ?></h4>
                <?php
                foreach ($value as $v) {
                    ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="<?= $key ?>" id="<?= $v ?>"
                               value="<?= $v ?>">
                        <label class="form-check-label" for="<?= $v ?>">
                            <?= $v ?>
                        </label>
                    </div>
                    <?php
                }
            }
            ?>
            <hr/>

            <?php
            $qs_count = 0;
            foreach ($questions as $heading => $qs) {
                ?>
                <h4><?= $heading ?></h4>
                <?php
                foreach ($qs as $q) {
                    ?>
                    <b><?= $q['text'] ?></b>
                    <?php
                    $ans_count = 0;
                    foreach ($q['answers'] as $ans) {
                        $main_control = $q['multiresponse'] ? 'checkbox' : 'radio';
                        $qs_name = $q['multiresponse'] ? "qs_{$qs_count}[]" : "qs_{$qs_count}";
                        ?>
                        <div class="form-check">
                            <input class="form-check-input" type="<?= $main_control ?>" name="<?= $qs_name ?>"
                                   id="<?= "qs_{$qs_count}_ans_{$ans_count}" ?>"
                                   value="<?= $ans ?>">
                            <?php
                            if ($ans == 'textfield') {
                                echo sprintf('<input type="text" class="form-control-sm textfield" name="textfield_%d" disabled />', $qs_count);
                            } else {
                                ?>
                                <label class="form-check-label" for="<?= "qs_{$qs_count}_ans_{$ans_count}" ?>">
                                    <?= $ans ?>
                                </label>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                        $ans_count++;
                    }
                    echo '<br/>';
                    $qs_count++;
                }
                echo '<hr/>';
            }
            ?>
            <button type="submit" class="btn btn-primary">Submit</button>
            <button type="button" class="btn btn-secondary">Reset</button>
        </form>
        <?php
    } else {
        ?>
        <div class="output">
            <p><?=$variables['output header']?></p>
            <h3 style="text-align: center;"><?=$variables['form title']?></h3>
            <?php
            $qs_count = 0;
            foreach ($questions as $heading => $qs) {
                echo sprintf('<b><u>%s</u></b><br/>', $heading);
                foreach($qs as $q) {
                    echo $arr_answers[$qs_count]." ";
                    $qs_count++;
                }
                echo '<br>';
            }
            ?>
            <p><br><?=$variables['output footer']?></p>
            <?php
            if($variables['mail report']) {
                ?>
                <button type="button" class="btn btn-primary btnEmail">Email</button>
                <?php
            }
            ?>
            <a href="<?=$_SERVER['PHP_SELF'].'?r=1'?>" class="btn btn-secondary">Reset</a>
        </div>
        <?php
    }
    ?>
</div>

<script src="js/jquery-3.3.1.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script type="text/javascript">
    $(function () {
        $('input[type="checkbox"], input[type="radio"]').on('change', function (e) {
            if ($(this).next('.textfield').length > 0) {
                $(this).next('.textfield').prop('disabled', !$(this).is(':checked'));
            }
        });

        $('.btnEmail').on('click', function(e) {
            e.preventDefault();
            $.post( "ajax.php", {
                email: "<?=$variables['email']?>",
                subject: "Results",
                body: "<?=$variables['mail header'].'<br><br>'.$variables['mail body']?>" + $('.output').html()
            })
                .done(function( data ) {
                    alert("Email sent");
                });
        });
    });
</script>
</body>
</html>



