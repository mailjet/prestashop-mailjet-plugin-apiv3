<script type="text/javascript">
function $_GET(key, default_)
{
	if (default_==null) default_="";
	key = key.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
	var regex = new RegExp("[\\?&]"+key+"=([^&#]*)");
	var qs = regex.exec(window.location.href);
	if(qs == null) return default_; else return qs[1];
}
parameters = $_GET('parameters');
params = parameters.split('_');

token = params[0];
uri = params[1];

if (params.length>2)
	for (i=2;i<params.length;i++)
		uri+= '_'+params[i];

window.top.location.href = 'http://<?=$_SERVER['HTTP_HOST']?>'+uri+'/index.php?tab=AdminModules&configure=mailjet&module_name=mailjet&MJ_request_page=CAMPAIGN2&token='+token;
</script>