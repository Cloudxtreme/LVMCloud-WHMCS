<link href='//fonts.googleapis.com/css?family=Open+Sans:300' rel='stylesheet' type='text/css'>
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
<script src="//code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>

<style>
    body {
        background-color: lightgrey;
        font-family: 'Open Sans', sans-serif;
        font-size: 16px;
    }

    #selectproduct, #container {
        text-align: center;
    }

    #selectproduct {
        font-weight: bold;
    }

    hr {border: 1px dashed grey; height: 0; width: 60%;}

    .cb-row {margin: 10px;clear:both;overflow:hidden;}
    .cb-row label {float:left;}
    .cb-row input {float:left;}

    #pBox {
        margin: 0 auto auto;
        text-align: center;
        width: 450px;
    }

    a {color: rgba(2, 1, 18, 0.63);}      /* unvisited link */
    a:visited {color:rgba(2, 1, 18, 0.63);;}  /* visited link */
    a:hover {color:rgba(2, 1, 18, 0.63);;}  /* mouse over link */
    a:active {color:rgba(2, 1, 18, 0.63);;}  /* selected link */
</style>


<script>
    $(document).ready(function() {
        $('#selecctall').click(function(event) {  //on click
            if(this.checked) { // check select status
                $('.checkbox1').each(function() { //loop through each checkbox
                    this.checked = true;  //select all checkboxes with class "checkbox1"
                });
            }else{
                $('.checkbox1').each(function() { //loop through each checkbox
                    this.checked = false; //deselect all checkboxes with class "checkbox1"
                });
            }
        });

    });
</script>