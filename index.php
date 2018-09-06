<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Documentation Automation</title>
</head>
<body>
    <link rel="stylesheet" href="css/bootstrap.min.css">
   
<!--
    <form action="./note.php" method="post">
      Pick a form you would like to work on:
       <p>
        <button type="submit" class="btn btn-primary" form="what_form" value="da">Diagnostic Assessment</button>
        <button type="submit" class="btn btn-primary" form="what_form" value="pn">Progress Note</button>
        </p>
    </form>
-->
    <form action="./note.php" method="get">
      Pick a form you would like to work on:
        <input type="radio" name="which_form" value="da"> Diagnostic Assessment
        <input type="radio" name="which_form" value="pn"> Progress Note
       <p>
        <button type="submit" class="btn btn-primary" value="submit">Open Form</button>
        </p>
    </form>

    
            
</body>
</html>