<?php
use yii\helpers\Url;
use yii\helpers\Html;

$a = 'abcdefghijklmnopqrstuvwxyz';
$b = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0';
$c = '0123456789';

$pw = '';

for ($i=0; $i<3; $i++) {
    $pw .= $a[mt_rand(0, strlen($a)-1)];   
}

for ($i=0; $i<3; $i++) {
    $pw .= $b[mt_rand(0, strlen($b)-1)];   
}

for ($i=0; $i<3; $i++) {
    $pw .= $c[mt_rand(0, strlen($c)-1)];   
}

$js = <<<EOT

$("form").submit(function(e) {
	e.preventDefault();
	
	self = $(this);

	new simpleAlert({title:"Console", content:"Please wait a moment"});

	$.post(self.attr("action"), self.serialize()).done(function() {
		console.log("Done");
	});
	
	window.setTimeout(function() {
	
	new simpleAlert({title:"Console", content:"Console was successfuly created for you<br>Note: Please allow us to open a new tab on your browser"});

	window.open(baseUrl + "web/console/index.html?host=console.autovm.net&port={$port}&password=" + self.find("#vncpw").val());
	
	}, 7000);
});

$("form").submit();

EOT;

$this->registerJs($js);
?>

<style type="text/css">
    .extend-table {
        box-shadow: none !important;
        border: 0 !important;
    }

    .extend-table td {
        border: 0 !important;
    }
</style>
<?php echo Html::beginForm(Url::toRoute(['console']));?>
<table class="table extend-table" style="display:none;">
    <tbody>
    <input type="hidden" name="vpsId" value="<?php echo $vpsId;?>">
    <input type="hidden" name="port" class="form-control" value="<?php echo $port;?>">
    <tr>
        <td>
            <input type="hidden" name="password" id="vncpw" class="form-control" value="<?php echo $pw;?>">
        </td>
    </tr>
    <tr>
        <td>
            <button type="submit" class="btn btn-success">Login</button>
        </td>
    </tr>
    </tbody>
</table>
<?php echo Html::endForm();?>