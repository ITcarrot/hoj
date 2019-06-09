$.fn.hoj_compile_result = function() {
	this.each(function(){
		var content = $(this).html();
		var pregs = [
			[/([^:\n]+)[:(]([0-9]+)[:,]([0-9]+)[:)] ([eE]rror|Fatal|fatal error): ([\s\S]*?)\n/, '<span class="text-danger">$1: Line $2 Column $3: Error: $5</span>\n'],
			[/([^:\n]+)[:(]([0-9]+)[:,]([0-9]+)[:)] [wW]arning: ([\s\S]*?)\n/, '<span class="text-warning">$1: Line $2 Column $3: Warning: $4</span>\n'],
			[/([^:\n]+)[:(]([0-9]+)[:,]([0-9]+)[:)] [nN]ote: ([\s\S]*?)\n/, '<span class="text-info">$1: Line $2 Column $3: Note: $4</span>\n'],
			[/([^:\n]+)[:(]([0-9]+)[:,]([0-9]+)[:)] /,'$1: Line $2 Column $3: '],
			[/\n(SyntaxError: [\s\S]*?)\n/, '\n<span class="text-danger">$1</span>\n']
		];
		for(var i = 0;i < pregs.length; i++)
			while(pregs[i][0].test(content))
				content = content.replace(pregs[i][0], pregs[i][1]);
		$(this).html(content);
		window.English_Compile_Result = content;
		
		var translate = [
			[/Line ([0-9]+) Column ([0-9]+): /,'第$1行第$2列：'],
			[/(Error|Fatal): /,'错误：'],
			[/Warning: /,'警告：'],
			[/Note: /,'提示：'],
			[/This language is not supported yet./,'暂不支持该语言'],
			[/Illegal Keywords Found/,'发现不合法的关键字'],
			[/Compiler Time Limit Exceeded/,'编译超时'],
			[/Compiler Memory Limit Exceeded/,'编译器内存超出限制'],
			[/Compiler Output Limit Exceeded/,'编译输出文件超出限制'],
			[/Compiler Dangerous Syscalls/,'编译器出现非法行为'],
			//c, c++
			[/In file included /,'在引用的文件中：'],
			[/required from ‘([\s\S]+?)’/,'从‘$1’引用'],
			[/required from here/,'从这里引用'],
			[/At global scope:/,'在全局'],
			[/In function ‘([\s\S]+?)’:/,'在函数‘$1’中：'],
			[/In member function ‘([\s\S]+?)’:/,'在成员函数‘$1’中：'],
			[/In static member function ‘([\s\S]+?)’:/,'在静态成员函数‘$1’中：'],
			[/In instantiation of ‘([\s\S]+?)’:/,'在初始化‘$1’时：'],
			[/compilation terminated\./,'编译终止'],
			//error
			[/ in namespace ’/,'在命名空间'],
			[/ has not been declared/,'未被定义'],
			[/ was not declared in this scope/,'在此范围中未被定义'],
			[/duplicate ‘([\s\S]+?)’/,'双重‘$1’'],
			[/redefinition of ‘([\s\S]+?)’/,'重复定义‘$1’'],
			[/‘([\s\S]+?)’ redeclared as different kind of symbol/,'‘$1’被重复定义成不一样的类型'],
			[/‘([\s\S]+?)’ previously (declared|defined) here/,'‘$1’之前在这里被定义'],
			[/previous declaration of ‘([\s\S]+?)’/,'‘$1’之前的定义'],
			[/‘([\s\S]+?)’ does not name a type/,'‘$1’并不是一个类型的名字'],
			[/invalid types ‘([\s\S]+?)’ for array subscript/,'无效的数组下标类型‘$1’'],
			[/array bound is not an integer constant before ‘([\s\S]+?)’ token/,'数组界限在‘$1’前不是一个整型定量'],
			[/ISO C\+\+ forbids declaration of ‘([\s\S]+?)’ with no type/,'ISO C++不允许定义没有类型的‘$1’'],
			[/‘([\s\S]+?)’ is too long for GCC/,'‘$1’对于GCC来说太长了'],
			[/name lookup of ‘([\s\S]+?)’ changed for ISO ‘([\s\S]+?)’ scoping/,'ISO标准下，‘$1’的作用域仅限于‘$2’'],
			[/#error This file requires compiler and library support for the ISO C\+\+ 2011 standard\. This support is currently experimental, and must be enabled with the -std=c\+\+11 or -std=gnu\+\+11 compiler options\./,'#错误，这个文件需要ISO C++2011标准的编译器和库。这项支持目前是实验性的，必须使用编译选项-std=c++11或-std=gnu++11来启用。'],
			[/No such file or directory/,'没有那个文件或目录'],
			[/no matching function for call to ‘([\s\S]+?)’/,'没有符合的函数去匹配调用‘$1’'],
			[/no match for ‘([\s\S]+?)’ \(operand type is ‘([\s\S]+?)’\)/,'没有匹配的‘$1’（操作的类型是‘$2’）'],
			[/no match for ‘([\s\S]+?)’ \(operand types are ‘([\s\S]+?)’ and ‘([\s\S]+?)’\)/,'没有匹配的‘$1’（操作的类型是‘$2’和‘$3’）'],
			[/no ‘([\s\S]+?)’ declared for postfix ‘([\s\S]+?)’/,'没有为后缀‘$2’定义‘$1’'],
			[/‘([\s\S]+?)’ has no member named ‘([\s\S]+?)’/,'‘$1’没有一个成员叫‘$2’'],
			[/expected declaration before ‘([\s\S]+?)’ token/,'期待在‘$1’前读到定义'],
			[/expected primary-expression before ‘([\s\S]+?)’ token/,'期待在字符‘$1’前读到一个表达式'],
			[/expected ‘([\s\S]+?)’ before ‘([\s\S]+?)’( token){0,1}/,'期待在‘$2’前读到‘$1’'],
			[/expected constructor, destructor, or type conversion before ‘([\s\S]+?)’ token/,'期待在‘$1’前读到构造函数、析构函数或类型转换'],
			[/expected type-specifier before ‘([\s\S]+?)’/,'期待在‘$1’前读到一个类型说明符'],
			[/expected unqualified-id before ['‘]([\s\S]+?)['’]( token){0,1}/,'期待在‘$1’前读到一个未定义的符号'],
			[/expected ‘([\s\S]+?)’ after struct definition/,'期待在定义struct之后读到‘$1’'],
			[/expected ‘([\s\S]+?)’ at end of input/,'期待在输入的最后读到‘$1’'],
			[/expected initializer before ‘([\s\S]+?)’( token){0,1}/,'期待在‘$1’前读到初始化的语句'],
			[/expected initializer at end of input/,'期待在文末读到初始化的语句'],
			[/missing terminating (.) character/,'丢失终止字符$1'],
			[/‘([\s\S]+?)’ without a previous ‘([\s\S]+?)’/,'‘$1’之前没有‘$2’'],
			[/found ‘([\s\S]+?)’ in nested-name-specifier, expected ‘([\s\S]+?)’/,'在嵌套名称指示符中读到‘$1’，但期望‘$2’'],
			[/invalid initialization of reference of type ‘([\s\S]+?)’ from expression of type ‘([\s\S]+?)’/,'不合法的初始化：在类型为‘$2’的表达式中引用‘$1’类型'],
			[/assignment of function ‘([\s\S]+?)’/,'指定函数‘$1’'],
			[/cannot convert ‘([\s\S]+?)’ to ‘([\s\S]+?)’ in assignment/,'指定函数时不能将‘$1’转化为‘$2’'],
			[/invalid conversion from ‘([\s\S]+?)’ to ‘([\s\S]+?)’/,'不能将‘$1’转换成‘$2’'],
			[/incompatible types in assignment of ‘([\s\S]+?)’ to ‘([\s\S]+?)’/,'表达式中从‘$1’到‘$2’的类型不兼容'],
			[/invalid operands of types ‘([\s\S]+?)’ and ‘([\s\S]+?)’ to binary ‘([\s\S]+?)’/,'类型‘$1’和‘$2’不能调用‘$3’'],
			[/invalid type argument of unary ‘([\s\S]+?)’ \(have ‘([\s\S]+?)’\)/,'不合法的一元‘$1’的参数类型（读到了‘$2’）'],
			[/lvalue required as left operand of assignment/,'表达式左边需要是一个“左值”'],
			[/provided for ‘([\s\S]+?)’/,'为‘$1’提供'],
			[/wrong number of template arguments \(([0-9]+), should be ([0-9]+)\)/,'模板参数个数不正确（有$1个，但应该是$2个）'],
			[/stray ‘\\([0-9]*)’ in program/,'程序中有游离的‘\\$1’'],
			[/return-statement with no value, in function returning ‘([\s\S]+?)’/,'在返回‘$1’的函数中返回空值'],
			[/#include nested too deeply/,'包含文件嵌套得太深了'],
			//warning
			[/unused variable ‘([\s\S]+?)’/,'变量‘$1’未被使用'],
			[/variable ‘([\s\S]+?)’ set but not used/,'变量‘$1’被设置了但未被使用'],
			[/ may be used uninitialized in this function/,'在这个函数中可能未初始化就使用了'],
			[/no return statement in function returning non-void/,'返回值不为空的函数中没有返回值'],
			[/control reaches end of non-void function/,'返回值不为空的函数中可能没有返回值'],
			[/suggest parentheses around ‘([\s\S]+?)’ inside ‘([\s\S]+?)’/,'建议给‘$2’中的‘$1’加括号'],
			[/operation on ‘([\s\S]+?)’ may be undefined/,'对于‘$1’的操作可能是不确定的'],
			[/bad option ‘([\s\S]+?)’ to pragma ‘([\s\S]+?)’/,'选项$1对于编译属性$2无效'],
			[/bad option ‘([\s\S]+?)’ to attribute ‘([\s\S]+?)’/,'选项$1对于属性$2无效'],
			[/pointer to a function used in arithmetic/,'在运算中使用指向函数的指针'],
			[/comparison between signed and unsigned integer expressions/,'比较有符号和无符号整数的表达式'],
			[/multi-character character constant/,'在一个字符常量中出现多个字符'],
			[/ constant is too large for its type/,'常量对于它的类型来说太大了'],
			[/this decimal constant is unsigned only in ISO C90/,'只有在ISO C90标准下，这个十进制常量才是无符号的'],
			[/extended initializer lists only available with -std=c\+\+11 or -std=gnu\+\+11/,'拓展初始化列表只在开启-std=c++11或-std=gnu++11时可用'],
			[/‘([\s\S]+?)’ changes meaning in C\+\+([0-9]+); please remove it/,'‘$1’的意思在C++$2中改变了，请不要使用它'],
			[/narrowing conversion of ‘([\s\S]+?)’ from ‘([\s\S]+?)’ to ‘([\s\S]+?)’ inside { } is ill-formed in C\+\+([0-9]+)/,'在{ }中将‘$1’从‘$2’缩小转换到‘$3’在C++$4中是病态的'],
			[/this ‘([\s\S]+?)’ clause does not guard\.\.\./,'这个$1语句并不引领……'],
			[/array subscript has type ‘([\s\S]+?)’/,'数组下标的类型是‘$1’'],
			[/format ‘([\s\S]+?)’ expects argument of type ‘([\s\S]+?)’, but argument ([0-9]+) has type ‘([\s\S]+?)’/,'格式‘$1’期望参数类型是‘$2’，但第$3个参数的类型是‘$4’'],
			//note
			[/in definition of macro ‘([\s\S]+?)’/,'在宏‘$1’的定义中'],
			[/in expansion of macro ‘([\s\S]+?)’/,'在宏‘$1’的拓展中'],
			[/if you use ‘([\s\S]+?)’ G\+\+ will accept your code/,'如果你打开‘$1’编译开关，G++会接受你的代码'],
			[/\.\.\.this statement, but the latter is misleadingly indented as if it were guarded by the ‘([\s\S]+?)’/,'……这些语句，后面那些语句的缩进并不正确，使人以为它是属于‘$1’的'],
			[/candidates are:/,'可能是：'],
			[/suggested alternative:/,'推荐相似的：'],
			[/template argument deduction\/substitution failed:/,'模板参数推导/替换失败：'],
			[/deduced conflicting types for parameter (‘[\s\S]+?’ \([\s\S]+?\))/,'推导参数$1时类型冲突'],
			[/mismatched types ‘([\s\S]+?)’ and ‘([\s\S]+?)’/,'类型‘$1’和‘$2’不匹配'],
			[/‘([\s\S]+?)’ is not derived from ‘([\s\S]+?)’/,'‘$1’并不是从‘$2’派生的'],
			//python
			[/File "answer\.code", line ([0-9]+)/,'代码的第$1行'],
			//error
			[/Syntax错误/,'语法错误'],
			[/invalid syntax/,'不合法的语法'],
			[/Missing parentheses in call to '([\s\S]+?)'. Did you mean ([\s\S]+?)\?/,'调用‘$1’时丢失了括号，你是否想写$2？'],
			[/Sorry: Indentation(Error: |错误：)unexpected indent \(answer\.code, line ([0-9]+)\)/,'对不起：缩进错误：不在期望中的缩进（代码第$2行）'],
			//pascal
			[/Compiling answer\.code/,'正在编译代码'],
			[/Compilation aborted/,'编译终止'],
			[/([\s\S]+?) returned an error exitcode/,'$1返回了一个错误的返回值'],
			[/Linking answer/,'正在输出程序'],
			[/\/usr\/bin\/ld\.bfd: warning: link\.res contains output sections; did you forget -T\?/,'/usr/bin/ld.bfd: 警告: link.res 含有输出节；您忘记了 -T？'],
			[/([0-9]+) lines compiled, ([0-9.]+) sec/,'编译了$1行，用时$2秒'],
			[/([0-9]+) warning\(s\) issued/,'出现了$1条错误'],
			[/([0-9]+) note\(s\) issued/,'出现了$1条提示'],
			//error
			[/Syntax error, "([\s\S]+?)" expected but "([\s\S]+?)" found/,'语法错误，期待“$1”但读到了“$2”'],
			[/Illegal char constant/,'不合法的字符常量'],
			[/Unexpected end of file/,'不在预期内的文件结尾'],
			[/Incompatible types: got "([\s\S]+?)" expected "([\s\S]+?)"/,'不兼容的类型：读到“$1”但期望“$2”'],
			[/Operator is not overloaded: "/,'运算符未重载：'],
			[/Identifier not found "([\s\S]+?)"/,'未找到“$1”的定义'],
			[/There were ([0-9]+) errors compiling module, stopping/,'编译模块出现了$1个错误，正在终止编译'],
			//warning
			[/Variable "([\s\S]+?)" does not seem to be initialized/,'变量“$1”看起来未被初始化'],
			//note
			[/Local variable "([\s\S]+?)" not used/,'局部变量“$1”未被使用']
		];//学习完所有CE记录的前十页
		for(var i = 0;i < translate.length; i++)
			while(translate[i][0].test(content))
				content = content.replace(translate[i][0], translate[i][1]);
		window.Chinese_Compile_Result = content;
		
		var trans_button = $('<a></a>');
		var elem = $(this);
		trans_button.addClass('btn btn-xs btn-primary bot-buffer-sm');
		trans_button.text('翻译');
		trans_button.click(function(){
			if($(this).text() == '翻译'){
				elem.html(Chinese_Compile_Result);
				$(this).text('取消翻译');
			}else{
				elem.html(English_Compile_Result);
				$(this).text('翻译');
			}
		});
		$(this).before(trans_button);
	});
}

$(document).ready(function(){
	$("#compile_result").hoj_compile_result();
});
