<?php
	function getreplace($wrong)
	{
		$replace=$wrong;
		$k=0;
		$wchar=array("缺","阙","生","升","燕","雁","蓬","篷","销","消","长","常","辩","辨","冽","洌","自","只","般","番","尽","近","即","既","避","敝","顷","倾","冥","暝","敲","推","朗","郎","风","烽","篱","离","瓜","果","落","洛","星","兴","综","踪","二","两","国","城","无","零","园","圆","灯","烛","先","现","间","见","为","未","闲","贤","逢","缝","海","洋","决","诀","瀛","赢","野","夜","宦","官","深","生","春","秋","欢","乐","惜","昔","鸟","雀","桃","李");
		for ($i=0;$i<count($wchar);$i++)
			if (strpos($wrong,$wchar[$i])!=false)
				$base[$k++]=$i;
		if ($k==0)
			return str_replace(substr($wrong,rand(0,3)*3,3),$wchar[rand(0,count($wchar)-1)],$wrong);
		$s=rand(0,$k-1);
		$s=$base[$s];
		$replace=str_replace($wchar[$s],$wchar[$s^1],$wrong);
		return $replace;
	}
	function getrandom()
	{
		$poem=fopen("/var/www/uoj/app/controllers/poem/resource","r");
		if ($poem==false)
		{
			$GLOBALS["badresource"]=true;
			return false;
		}
		$rand=rand(1,22);
		$i=0;
		while ($i<$rand)
		{
			$title=fgets($poem);
			$writer=fgets($poem);
			$line=1;
			while (1)
			{
				$content[$line]=fgets($poem);
				if ($content[$line][0]=="#")
					break;
				$line++;
			}
			$i++;
			if ($i<$rand)
				continue;
			$line--;
			$select=rand(1,$line);
			$first=rand(0,1);
			$result=substr($content[$select],$first*18,15);
		//	$GLOBALS["more"]="<p>".$content[$select]."</p><span style=\"font-size: 24px; line-height: 0px;\"><p>".$writer." 《".$title."》</p></span>";
			$GLOBALS["more"]="<span style=\"font-size: 24px; line-height: 0px;\"><p>".$writer." 《".$title."》</p></span>";
			break;
		}
		$GLOBALS["answer"]=$result;
		fclose($poem);
		$try=0;
		while ($try<20)
		{
			$bt=0;
			$same=rand(0,4);
			$char=substr($result,$same*3,3);
			$poem=fopen("/var/www/uoj/app/controllers/poem/resource","r");
			$i=0;
			while ($i<22)
			{
				$title=fgets($poem);
				$writer=fgets($poem);
				$line=1;
				while (1)
				{
					$content[$line]=fgets($poem);
					if ($content[$line][0]=="#")
						break;
					$first=substr($content[$line],0,15);
					$second=substr($content[$line],18,15);
					if ($i+1!=$rand)
					{
						if (strpos($first,$char))
							$base[$bt++]=$first;
						if (strpos($second,$char))
							$base[$bt++]=$second;
					}
					$line++;
				}
				$i++;
			}
			fclose($poem);
			$try++;
			if ($bt==0)
				continue;
			$bselect=rand(0,$bt-1);
			$wrong=$base[$bselect];
			$wrong=str_replace($char,"",$wrong);
			$replace=getreplace($wrong);
			$result=$result.$replace;
			return $result;
		}
		$poem=fopen("/var/www/uoj/app/controllers/poem/resource","r");
		$rand2=0;
		while ($rand2==$rand)
			$rand2=rand(1,22);
		$i=0;
		while ($i<$rand)
		{
			$title=fgets($poem);
			$writer=fgets($poem);
			$line=1;
			while (1)
			{
				$content[$line]=fgets($poem);
				if ($content[$line][0]=="#")
					break;
				$line++;
			}
			$i++;
			if ($i<$rand)
				continue;
			$line--;
			$select=rand(1,$line);
			$first=rand(0,1);
			$result2=substr($content[$select],$first*18,15);
			$result=$result.str_replace(substr($result2,rand(0,3)*3,3),"",$result2);
			break;
		}
		fclose($poem);
		return $result;
	}
	function resshuffle($str)
	{
		$order=array(0,1,2,3,4,5,6,7,8);
		shuffle($order);
		$new=$str;
		for ($i=0;$i<9;$i++)
		{
			$new[$i*3]=$str[$order[$i]*3];
			$new[$i*3+1]=$str[$order[$i]*3+1];
			$new[$i*3+2]=$str[$order[$i]*3+2];
		}
		return $new;
	}
	function getresult()
	{
		$GLOBALS["badresource"]=false;
		$result=getrandom();
		if ($GLOBALS["badresource"]==true)
			return __FILE__;
		while (strpos($result,"，")||strlen($result)!=27)
			$result=getrandom();
		return resshuffle($result);
	}
?>
				
