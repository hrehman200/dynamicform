<?php
$string = file_get_contents("./config.json");
$config = json_decode($string, true);
$variables = $config['variables'];
$form = $config['form'];
$questions = $config['questions'];

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

<div class="container">
    <h3><?= $variables['form title'] ?></h3>

    <form>
        <?php
        foreach($form as $key=>$value) {
            ?>
            <h4><?=$key?></h4>
            <?php
            foreach ($value as $v) {
                ?>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="<?=$key?>" id="<?=$v?>" value="<?=$v?>" >
                    <label class="form-check-label" for="<?=$v?>">
                        <?=$v?>
                    </label>
                </div>
            <?php
            }
        }
        ?>
        <hr/>

        <?php
        $qs_count = 0;
        foreach($questions as $heading=>$qs) {
            ?>
            <h4><?=$heading?></h4>
            <?php
            foreach ($qs as $q) {
                ?>
                <b><?=$q['text']?></b>
                <?php
                $ans_count = 0;
                foreach ($q['answers'] as $ans) {
                    $main_control = $q['multiresponse'] ? 'checkbox' : 'radio';
                    $sub_control = '';
                    ?>
                    <div class="form-check">
                        <input class="form-check-input" type="<?=$main_control?>" name="qs_<?= $qs_count ?>" id="<?="qs_{$qs_count}_ans_{$ans_count}"?>"
                               value="<?= $v ?>">
                        <label class="form-check-label" for="<?="qs_{$qs_count}_ans_{$ans_count}"?>">
                            <?= $ans ?>
                        </label>
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
</div>

<script src="js/jquery-3.2.1.slim.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>



