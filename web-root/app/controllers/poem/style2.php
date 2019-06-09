<style type="text/css">
	body
	{
		margin: 0px;
		font-family: DefSans, Ubuntu-L;
		-webkit-text-stroke: 0.5px;
	}
	img
	{
		max-width: 100%;
	}
	a
	{
		text-decoration: none;
		color: inherit;
	}
	a:focus
	{
		outline: none;
		-moz-outline: none;
	}
	table
	{
		width: 100%;
	}
	pre
	{
		font-family: Consolas, HanSans-L;
		word-wrap: break-word;
		border-left: 10px;
		border-top: 0px;
		border-bottom: 0px;
		border-right: 0px;
		border-style: solid;
		padding-left: 10px;
	}
	blockquote
	{
		height: auto;
		width: auto;
		border-left: 10px;
		border-top: 0px;
		border-bottom: 0px;
		border-right: 0px;
		border-style: solid;
		padding-left: 10px;
	}
	pre
	{
		font-family: Consolas, HanSans-L;
		word-wrap: break-word;
		border-left: 10px;
		border-top: 0px;
		border-bottom: 0px;
		border-right: 0px;
		border-style: solid;
		padding-left: 10px;
	}
	hr
	{
		border: none;
		height: 3px;
		width: 100%;
		background-color: <?php echo "#".dex(255-0.3*(255-$r)).dex(255-0.3*(255-$g)).dex(255-0.3*(255-$b));?>;
	}
	.button
	{
		margin-top: 0px;
		margin-bottom: -6px;
		font-family: OpenSymbol, DefSans, Ubuntu-L;
		font-size: 24px;
		line-height: 28px;
		height: 36px;
		width: 36px;
		border-width: 1px;
		border-style: solid;
		border-radius: 18px;
		white-space: nowrap;
		text-align: center;
		float: left;
	}
	.tid
	{
		font-family: OpenSymbol, DefSans, Ubuntu-L;
		font-size: 24px;
		line-height: 36px;
		float: left;
	}
	.tik
	{
		max-width: 30%;
		margin: 20px;
		padding: 12px;
		font-family: OpenSymbol, DefSans, Ubuntu-L;
		font-size: 18px;
		line-height: 20px;
		width: auto;
		color: <?php echo "#".dex(0.8*$r).dex(0.8*$g).dex(0.8*$b);?>;
		border-color: <?php echo "#".dex(0.8*$r).dex(0.8*$g).dex(0.8*$b);?>;
		word-wrap: break-word;
		text-align: left;
		transition: all 0.5s ease 0s;
		-o-transition: all 0.5s ease 0s;
		-moz-transition: all 0.5s ease 0s;
		-webkit-transition: all 0.5s ease 0s;
	}
	.tik:hover
	{
		color: <?php echo "#".dex(255-0.1*(255-$r)).dex(255-0.1*(255-$g)).dex(255-0.1*(255-$b));?>;
		border-color: <?php echo "#".dex(255-0.1*(255-$r)).dex(255-0.1*(255-$g)).dex(255-0.1*(255-$b));?>;
		background-color: <?php echo "#".dex(255-0.8*(255-$r)).dex(255-0.8*(255-$g)).dex(255-0.8*(255-$b));?>;
	}
	.ntik
	{
		margin: 20px;
		padding: 12px;
		font-family: OpenSymbol, DefSans, Ubuntu-L;
		font-size: 18px;
		line-height: 8px;
		width: auto;
		color: <?php echo "#".dex(0.8*$r).dex(0.8*$g).dex(0.8*$b);?>;
		border-color: <?php echo "#".dex(0.8*$r).dex(0.8*$g).dex(0.8*$b);?>;
		white-space: nowrap;
		text-align: left;
	}
</style>