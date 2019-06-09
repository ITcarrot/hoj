		function draw()
		{
			var local=new Date().getTime();
			var canvas=document.getElementById('timecanvas');
			var context=canvas.getContext('2d');
			var date1=Date.parse(new Date("2019/11/09 08:30:00"));
			date1=date1/1000;
			var date2=Date.parse(new Date(local-localbegin+begin));
			date2=date2/1000;
			context.clearRect(0,0,1000,200);
			context.beginPath();
			context.arc(100,100,100,Math.PI*3/2,Math.floor(date1-date2)/3600/24/50*Math.PI+Math.PI*3/2,false);
			context.arc(100,100,98,Math.floor(date1-date2)/3600/24/50*Math.PI+Math.PI*3/2,Math.PI*3/2,true);
			context.closePath();
			context.fillStyle='#DA394E';
			context.fill();
			context.font='75px Helvetica';
			if (Math.floor((date1-date2)/3600/24)>9)
				context.fillText(Math.floor((date1-date2)/3600/24),45,115);
			else
				context.fillText(Math.floor((date1-date2)/3600/24),85,115);
			context.font='40px Helvetica';
			context.fillText('天',130,140);
			context.beginPath();
			context.arc(350,100,100,Math.PI*3/2,(Math.floor(date1-date2)/3600-Math.floor(Math.floor(date1-date2)/3600/24)*24)/12*Math.PI+Math.PI*3/2,false);
			context.arc(350,100,98,(Math.floor(date1-date2)/3600-Math.floor(Math.floor(date1-date2)/3600/24)*24)/12*Math.PI+Math.PI*3/2,Math.PI*3/2,true);
			context.closePath();
			context.fillStyle='#F0BE00';
			context.fill();
			context.font='75px Helvetica';
			if (Math.floor(Math.floor(date1-date2)/3600-Math.floor(Math.floor(date1-date2)/3600/24)*24)>9)
				context.fillText(Math.floor(Math.floor(date1-date2)/3600-Math.floor(Math.floor(date1-date2)/3600/24)*24),295,115);
			else
				context.fillText(Math.floor(Math.floor(date1-date2)/3600-Math.floor(Math.floor(date1-date2)/3600/24)*24),335,115);
			context.font='40px Helvetica';
			context.fillText('时',380,140);
			context.beginPath();
			context.arc(600,100,100,Math.PI*3/2,(Math.floor(date1-date2)/60-Math.floor(Math.floor(date1-date2)/3600)*60)/30*Math.PI+Math.PI*3/2,false);
			context.arc(600,100,98,(Math.floor(date1-date2)/60-Math.floor(Math.floor(date1-date2)/3600)*60)/30*Math.PI+Math.PI*3/2,Math.PI*3/2,true);
			context.closePath();
			context.fillStyle='#124E78';
			context.fill();
			context.font='75px Helvetica';
			if (Math.floor(Math.floor(date1-date2)/60-Math.floor(Math.floor(date1-date2)/3600)*60)>9)
				context.fillText(Math.floor(Math.floor(date1-date2)/60-Math.floor(Math.floor(date1-date2)/3600)*60),545,115);
			else
				context.fillText(Math.floor(Math.floor(date1-date2)/60-Math.floor(Math.floor(date1-date2)/3600)*60),585,115);
			context.font='40px Helvetica';
			context.fillText('分',630,140);
			context.beginPath();
			context.arc(850,100,100,Math.PI*3/2,(Math.floor(date1-date2)-Math.floor(Math.floor(date1-date2)/60)*60)/30*Math.PI+Math.PI*3/2,false);
			context.arc(850,100,98,(Math.floor(date1-date2)-Math.floor(Math.floor(date1-date2)/60)*60)/30*Math.PI+Math.PI*3/2,Math.PI*3/2,true);
			context.closePath();
			context.fillStyle='#20BF1E';
			context.fill();
			context.font='75px Helvetica';
			if (Math.floor(Math.floor(date1-date2)-Math.floor(Math.floor(date1-date2)/60)*60)>9)
				context.fillText(Math.floor(Math.floor(date1-date2)-Math.floor(Math.floor(date1-date2)/60)*60),795,115);
			else
				context.fillText(Math.floor(Math.floor(date1-date2)-Math.floor(Math.floor(date1-date2)/60)*60),835,115);
			context.font='40px Helvetica';
			context.fillText('秒',880,140);
		}

