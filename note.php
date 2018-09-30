<?php
error_reporting(E_ERROR);
//if($_GET['which_form'] == "da"){
//    echo "<h1>You loaded the " . $_GET['which_form'] . " form!</h1>";
//}


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

/**
 * Replaces a variable name in a string and returns the value
 *
 * @param $variable
 * @return mixed
 */
function getVariableValue($variable) {
    global $variables;

    preg_match_all('/\\[(.*?)\\]/', $variables[$variable], $matches, PREG_SET_ORDER);
    if(count($matches) > 0) {
        $value = $variables[$variable];
        for($i=0; $i<count($matches); $i++) {
            $other_variable_used = $matches[$i][1];
            $value = str_replace($matches[$i][0], $variables[$other_variable_used], $value);
        }
        return $value;
    }
    return $variables[$variable];
}

// load stuff from json
if($_REQUEST['which_form']){
    $load_form = $_REQUEST['which_form'];
}else{
    echo "<h1>A form wasn't selected or a configuration wasn't found!</h1>";
}
$string = file_get_contents("./" . $load_form . ".json");
$config = json_decode($string, true);
$variables = $config['variables'];
$form = $config['form'];
$questions = $config['questions'];

// if r=1 in url, reset the form
if(isset($_GET['r'])) {
    unset($_POST);
}

// if form submitted
if (count($_POST) > 0) {
    $gender = $_POST['Gender'];
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
//        case 'transgender male to female':
//            break;
//        case 'transgender female to male':
//            break;
    }

    // put all questions, irrespective of their heading, into one array, so that later we access a qs by numerical index
    $arr_questions = [];
    $arr_answers = [];
    foreach ($questions as $heading => $qs) {
        foreach ($qs as $q) {
            $arr_questions[] = $q;
        }
    }

    // loop each submitted form value
    foreach ($_POST as $key => $value) {
        // if the name of form value contains qs_
        if (stripos($key, 'qs_') !== false) {
            // extract the qs index
            $qs_index = str_replace('qs_', '', $key);
            if ($qs_index >= 0) {
                // get the question structure via index from questions array we created above
                $the_qs = $arr_questions[$qs_index];
                // pick a random response
                $response = $the_qs['responses'][array_rand($the_qs['responses'])];

                // if the qs is multiresponse, in which case array will be submitted from form
                if (is_array($value)) {

                    // go through each response, and if response if textfield, fetch value from textfield_[qs_index]
                    $value = array_map(function($v) use ($qs_index) {
                        if($v == 'textfield') {
                            $v = $_POST['textfield_'.$qs_index];
                        }
                        return $v;
                    }, $value);

                    // if response list's first item starts with *
                    if($value[0][0] == '*') {
                        $str_value = implode('<br/>', $value);
                        $result = str_replace('[response list]', $str_value, $response);
                    } else {
                        // combine all responses a, b, c like a, b and c
                        $str_value = implode(", ", $value);
                        $value = str_lreplace(", ", " and ", $str_value);
                        // $value = str_replace(", * ","<br>* ", $value);
                        $result = str_replace('[response list]', $value, $response);
                    }
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

                // add the constructed response to an array of answers
                $arr_answers[$qs_index] = ucfirst($result);
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
    <title><?= getVariableValue('form title') ?></title>
</head>
<body>

<div class="container" style="margin:30px;">

    <?php
    // if form not submitted, show first page with form
    if(count($_POST) == 0) {
        ?>
        <h3><?= getVariableValue('form title') ?></h3>

        <form method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="which_form" value="<?=$load_form?>" />
            <?php
            foreach ($form as $key => $value) {
                ?>
                <h4><?= $key ?></h4>
                <?php
                foreach ($value as $v) {
                    ?>
                    <div class="form-check <?=($variables['vertical_ans'] == 0) ? 'form-check-inline' : ''?>">
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
            // loop all questions and render them in a form
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
                        <div class="form-check <?=($variables['vertical_ans'] == 0) ? 'form-check-inline' : ''?>">
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
            <button type="button" class="btn btn-secondary" onClick="window.location.reload()">Reset</button>
        </form>
        <?php
    } else {   // if form submitted
        ?>
        <div class="output">
            <p><?=getVariableValue('output header')?></p>
            <h3 style="text-align: center;"><?=getVariableValue('form title')?></h3>
            <?php
            // go through all questions and display corresponding answers
            $qs_count = 0;
            foreach ($questions as $heading => $qs) {
                echo sprintf('<b><u>%s</u></b><br/>', $heading);
                foreach($qs as $q) {
                    echo $arr_answers[$qs_count]." ";
                    $qs_count++;
                }
                echo '<br/><br/>';
            }
            ?>
            <p><?=getVariableValue('output footer')?></p>
        </div>
        <?php
        if(getVariableValue('mail report')) {
            ?>
            <button type="button" class="btn btn-primary btnEmail">Email</button>
            <?php
        }
        ?>
        <a href="<?=$_SERVER['PHP_SELF'].'?r=1'?>" class="btn btn-secondary">Reset</a>
        <?php
    }
    ?>
</div>

<script src="js/jquery-3.3.1.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script type="text/javascript">
    $(function () {

        // if a radio button or checkbox beside textfield is clicked, enable that textfield
        $('input[type="checkbox"], input[type="radio"]').on('change', function (e) {
            if ($(this).next('.textfield').length > 0) {
                $(this).next('.textfield').prop('disabled', !$(this).is(':checked'));
            }
        });

        // send email on email click
        $('.btnEmail').on('click', function(e) {
            e.preventDefault();
            $.post( "ajax.php", {
                email: "<?=$variables['email']?>",
                subject: "Results",
                body: "<?=getVariableValue('mail header').'<br><br>'.getVariableValue('mail body')?>" + $('.output').html()
            })
                .done(function( data ) {
                    alert("Email sent");
                });
        });
    });
</script>
</body>
</html>



