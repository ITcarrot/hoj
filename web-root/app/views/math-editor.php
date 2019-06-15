<?php
	$types = array(
		'color' => '颜色',
		'big-op' => '大运算符',
		'mid-op' => '运算符',
		'bf-op' => '指示符',
		'cmp' => '逻辑运算符',
		'bracket' => '括号',
		'space' => '空格',
		'symbol' => '奇怪的符号',
		'sup' => '上下标',
		'arrow' => '箭头',
		'func' => '函数',
		'multi' => '多行表达式'
	);
	$math = array(
		'color' => array(
			array('\color{Red}{Red}','\color{Red}{}'),
			array('\color{Green}{Green}','\color{Green}{}'),
			array('\color{Blue}{Blue}','\color{Blue}{}'),
			array('\color{Yellow}{Yellow}','\color{Yellow}{}'),
			array('\color{Cyan}{Cyan}','\color{Cyan}{}'),
			array('\color{Magenta}{Magenta}','\color{Magenta}{}'),
			array('\color{Teal}{Teal}','\color{Teal}{}'),
			array('\color{Purple}{Purple}','\color{Purple}{}'),
			array('\color{DarkBlue}{DarkBlue}','\color{DarkBlue}{}'),
			array('\color{DarkRed}{DarkRed}','\color{DarkRed}{}'),
			array('\color{Orange}{Orange}','\color{Orange}{}'),
			array('\color{DarkOrange}{DarkOrange}','\color{DarkOrange}{}'),
			array('\color{Golden}{Golden}','\color{Golden}{}'),
			array('\color{Pink}{Pink}','\color{Pink}{}'),
			array('\color{DarkGreen}{DarkGreen}','\color{DarkGreen}{}'),
			array('\color{Orchid}{Orchid}','\color{Orchid}{}'),
			array('\color{Emerald}{Emerald}','\color{Emerald}{}')
		),
		'big-op' => array(
			array('{x}^{a}','{}^{}'),
			array('{x}_{a}','{}_{}'),
			array('{x}_{a}^{b}','{}_{}^{}'),
			array('{{x}_{a}}^{b}','{{}_{}}^{}'),
			array('_{a}^{b}\textrm{C}','_{}^{}\textrm{}'),
			array('\frac{a}{b}','\frac{}{}'),
			array('x\tfrac{d}{c}','\tfrac{}{}'),
			array('\int_{a}^{b}','\int_{}^{}'),
			array('\oint_{a}^{b}','\oint_{}^{}'),
			array('\iint_{a}^{b}','\iint_{}^{}'),
			array('\bigcap_{a}^{b}','\bigcap_{}^{}'),
			array('\bigcup_{a}^{b}','\bigcup_{}^{}'),
			array('\lim_{x \to 0}','\lim_{}'),
			array('\sum_{a}^{b}','\sum_{}^{}'),
			array('\sqrt[n]{x}','\sqrt[]{}'),
			array('\prod_{a}^{b}','\prod_{}^{}'),
			array('\coprod_{a}^{b}','\coprod_{}^{}')
		),
		'mid-op' => array(
			'\pm ', '\mp ', '\times ', '\ast ', '\div ', '\cap ', '\cup ', '\cdot ', '\Cap ', '\Cup ', '\uplus ', '\sqcap ', '\sqcup ', '\bigsqcup ', '\wedge ', '\vee ', '\barwedge ', '\veebar ', '\bigtriangleup ', '\bigtriangledown ', '\triangleleft ', '\triangleright ', '\setminus ', '\dotplus ', '\amalg ', '\dagger ', '\ddagger ', '\wr ', '\star ', '\bigstar ', '\lozenge ', '\blacklozenge ', '\circ ', '\bullet ', '\bigcirc ', '\square ', '\blacksquare ', '\triangle ', '\triangledown ', '\blacktriangle ', '\blacktriangledown ', '\bigoplus ', '\bigotimes ', '\bigodot ', '\diamond ', '\ominus ', '\oplus ', '\circledcirc ', '\oslash ', '\otimes ', '\circledast ', '\circleddash ', '\odot'
		),
		'bf-op' => array(
			'\because ', '\therefore ', '\cdots ', '\ddots ', '\vdots ', '\S ', '\partial ', '\mathbb{P}', '\mathbb{N}', '\imath ', '\jmath ', '\mathbb{Z}', '\angle ', '\measuredangle ', '\sphericalangle ', '\Re ', '\mathbb{I}', '\Im ', '\mathbb{Q}', '\mathbb{R}', '\forall ', '\exists ', '\varnothing ', '\infty ', '\mho ', '\wp ', '\mathbb{C}' , '\top '
		),
		'cmp' => array(
			'< ', '> ', '\leq ', '\geq ', '\leqslant ', '\geqslant ', '\nless ', '\ngtr ', '\nleqslant ', '\ngeqslant ', '= ', '\doteq ', '\equiv ', '\neq ', '\not\equiv ', '\prec ', '\succ ', '\preceq ', '\succeq ', '\ll ', '\gg ', '\sim ', '\approx ', '\simeq ', '\cong ', '\vdash ', '\dashv ', '\smile ', '\frown ', '\models ', '\perp ', '\mid ', '\parallel ', '\asymp ', '\propto ', '\bowtie ', '\Join', '\sqsubset ', '\sqsupset ', '\sqsubseteq ', '\sqsupseteq ', '\subset ', '\supset ', '\subseteq ', '\supseteq ', '\nsubseteq ', '\nsupseteq ', '\subseteqq ', '\supseteqq ', '\nsubseteq ', '\nsupseteqq ', '\in ', '\ni ', '\notin '
		),
		'bracket' => array(
			'( )', '[ ]', '\{ \}', '| |', '\| \|', '\langle  \rangle ', '\lfloor  \rfloor ', '\lceil  \rceil '
		),
		'space' => array(
			array('H\, O\, J','\, '),array('H\: O\: J','\: '),
			array('H\; O\; J','\; '),array('H\! O\! J','\! ')
		),
		'symbol' => array(
			'\alpha ', '\beta ', '\gamma ', '\delta ', '\epsilon ', '\varepsilon ', '\zeta ', '\eta ', '\theta ', '\vartheta ', '\iota ', '\kappa ', '\lambda ', '\mu ', '\nu ', '\xi ', '\pi ', '\varpi ', '\rho ', '\varrho ', '\sigma ', '\varsigma ', '\tau ', '\upsilon ', '\phi ', '\varphi ', '\chi ', '\psi ', '\omega ', '\Gamma ', '\Delta ', '\Theta ', '\Lambda ', '\Xi ', '\Pi ', '\Sigma ', '\Upsilon ', '\Phi ', '\Psi ', '\Omega ', '\$ '
		),
		'sup' => array(
			array('{a}\' ','{}\' '),array('{a}\'\' ','{}\'\' '),
			array('\dot{a}','\dot{}'),array('\ddot{a}','\ddot{}'),
			array('\hat{a}','\hat{}'),array('\check{a}','\check{}'),
			array('\grave{a}','\grave{}'),array('\acute{a}','\acute{}'),
			array('\tilde{a}','\tilde{}'),array('\breve{a}','\breve{}'),
			array('\bar{a}','\bar{}'),array('\vec{a}','\vec{}'),
			array('\not{a}','\not{}'),array('{a}^{\circ}','{}^{\circ}'),
			array('\widetilde{abc}','\widetilde{}'),array('\widehat{abc}','\widehat{}'),
			array('\overleftarrow{abc}','\overleftarrow{}'),array('\overrightarrow{abc}','\overrightarrow{}'),
			array('\overline{abc}','\overline{}'),array('\underline{abc}','\underline{}'),
			array('\overbrace{abc}','\overbrace{}'),array('\underbrace{abc}','\underbrace{}'),
			array('\overset{a}{abc}','\overset{}{}'),array('\underset{a}{abc}','\underset{}{}')
		),
		'arrow' => array(
			'\mapsto ', '\to ', '\leftarrow ', '\rightarrow ', '\Leftarrow ', '\Rightarrow ', '\leftrightarrow ', '\Leftrightarrow ', '\leftharpoonup ', '\rightharpoonup ', '\leftharpoondown ', '\rightharpoondown ', '\leftrightharpoons ', '\rightleftharpoons ',
			array('\xleftarrow[text]{long}','\xleftarrow[]{}'),array('\xrightarrow[text]{long}','\xrightarrow[]{}')
		),
		'func' => array(
			'\sin ', '\cos ', '\tan ', '\csc ', '\sec ', '\cot ', '\sinh ', '\cosh ', '\tanh ', '\coth ', '\arcsin ', '\arccos ', '\arctan ', '\textrm{arccsc}', '\textrm{arcsec}', '\textrm{arccot}', '\sin^{-1}', '\cos^{-1}', '\tan^{-1}', '\sinh^{-1}', '\cosh^{-1}', '\tanh^{-1}', '\exp ', '\lg ', '\ln ', '\log ', '\log_{e}', '\log_{10}', '\lim ', '\liminf ', '\limsup ', '\max ', '\min ', '\infty ', '\arg ', '\det ', '\dim ', '\gcd ', '\hom ', '\ker ', '\Pr ', '\sup '
		),
		'multi' => array(
			array("\\begin{matrix}\nH & O & J\\\\ \nH & O & J\n\\end{matrix}","\\begin{matrix}\n &  & \\\\ \n &  & \n\\end{matrix}"),
			array("\\begin{bmatrix}\nH & O & J\\\\ \nH & O & J\n\\end{bmatrix}","\\begin{bmatrix}\n &  & \\\\ \n &  & \n\\end{bmatrix}"),
			array("\\begin{pmatrix}\nH & O & J\\\\ \nH & O & J\n\\end{pmatrix}","\\begin{pmatrix}\n &  & \\\\ \n &  & \n\\end{pmatrix}"),
			array("\\bigl(\\begin{smallmatrix}\nH & O & J\\\\ \nH & O & J\n\\end{smallmatrix}\\bigr)","\\bigl(\\begin{smallmatrix}\n &  & \\\\ \n &  & \n\\end{smallmatrix}\\bigr)"),
			array("\\begin{vmatrix}\nH & O & J\\\\ \nH & O & J\n\\end{vmatrix}","\\begin{vmatrix}\n &  & \\\\ \n &  & \n\\end{vmatrix}"),
			array("\\begin{Bmatrix}\nH & O & J\\\\ \nH & O & J\n\\end{Bmatrix}","\\begin{Bmatrix}\n &  & \\\\ \n &  & \n\\end{Bmatrix}"),
			array("\\begin{Vmatrix}\nH & O & J\\\\ \nH & O & J\n\\end{Vmatrix}","\\begin{Vmatrix}\n &  & \\\\ \n &  & \n\\end{Vmatrix}"),
			array("\\left\\{\\begin{matrix}\nH & O & J\\\\ \nH & O & J\n\\end{matrix}\\right.","\\left\\{\\begin{matrix}\n &  & \\\\ \n &  & \n\\end{matrix}\\right."),
			array("\\left.\\begin{matrix}\nH & O & J\\\\ \nH & O & J\n\\end{matrix}\\right\\}","\\left.\\begin{matrix}\n &  & \\\\ \n &  & \n\\end{matrix}\\right\\}"),
			array("\\left.\\begin{matrix}\nH & O & J\\\\ \nH & O & J\n\\end{matrix}\\right|","\\left.\\begin{matrix}\n &  & \\\\ \n &  & \n\\end{matrix}\\right|"),
			array('\binom{n}{r}','\binom{}{}'),
			array("\\begin{cases}\nHorseOJ & \\text{ if } name=mid \\\\ \nHOJ & \\text{ if } name=short \n\\end{cases}","\\begin{cases}\n & \\text{ if } x= \\\\ \n & \\text{ if } x= \n\\end{cases}"),
			array("\\begin{align*}\n1+1 &= 2\\\\ \n2 &= 2\n\\end{align*}","\\begin{align*}\n &= \\\\ \n &= \n\\end{align*}")
		)
	);
?>
<hr/>
<style>
	.select-math{
		display:inline-block;
		padding:0 20px;
	}
</style>
<div class="row text-center">
	<ul class="nav nav-tabs">
		<?php foreach($types as $id => $name): ?>
			<li><a href="#tab-<?= $id ?>" data-toggle="tab"><?= $name ?></a></li>
		<?php endforeach ?>
	</ul>
	<div class="top-buffer-md"></div>
	<div class="col-md-6"><div class="tab-content">
		<?php foreach($types as $id => $name): ?>
			<div class="tab-pane" id="tab-<?= $id ?>"></div>
		<?php endforeach ?>
	</div></div>
	<div class="col-md-6">
		<textarea class="form-control" id="input-math" oninput="update()" style="min-height:150px"></textarea>
		<div id="view-math" style="overflow:auto"></div>
	</div>
</div>
<script>
	function add(text){
		text = text[0];
		var input = $('#input-math')[0];
		var endPos = input.selectionEnd;
		if (isNaN(endPos)){
			input.value += ltext + rtext;
		} else {
			input.value = input.value.substring(0, endPos) + text + input.value.substring(endPos, input.value.length);
			input.selectionStart = input.selectionEnd = endPos + text.length;
		}
		update();
	}
	var preview_timer = -1;
	function update(){
		clearTimeout(preview_timer);
		preview_timer = setTimeout(function(){
				$('#view-math').html('$$' + $('#input-math').val() + '$$');
				MathJax.Hub.Queue(["Typeset", MathJax.Hub]);
			},500);
	}
	var maths = <?= json_encode($math) ?>;
	$.each(maths,function(id, content){
		$.each(content,function(curid, cur){
			var obj = $('<a></a>');
			obj.addClass('select-math');
			if($.isArray(cur)){
				obj.attr('onclick','add(' + JSON.stringify([cur[1]]) + ')');
				obj.attr('title',cur[0]);
				obj.text('$$' + cur[0] + '$$');
			}else{
				obj.attr('onclick','add(' + JSON.stringify([cur]) + ')');
				obj.attr('title',cur);
				obj.text('$$' + cur + '$$');
			}
			$('#tab-' + id).append(obj);
		});
	});
</script>