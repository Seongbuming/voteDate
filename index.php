<?php
session_start();

require("func/mysqli.php");

$start_of_date = 1;
$end_of_date = 31;

$sql_voteddata = $mysqli->query("
	SELECT
		CAST(RIGHT(Date, 2) AS INTEGER) Date_,
		User,
		Reason
	FROM
		date_data
	WHERE
		Date >= '2016-08-01'
		AND Date <= '2016-08-31'
		AND Valid = 1
	ORDER BY Date ASC");
$num_voteddatas = $sql_voteddata->num_rows;
?>
<html>
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
		<meta name="viewport" content="width=device-width, user-scalable=no">
		<link rel="stylesheet" type="text/css" href="./style.css" />
		<title>날짜 투표</title>
		<script src="./js/jquery-1.11.1.min.js"></script>
		<script type="text/javascript">
			var step = 1;
			var dates = new Array(<?=$end_of_date+1?>);
			var voteddata = new Array(<?=$num_voteddatas?>);

			for(var i = 0; i < <?=$end_of_date+1?>; i++)
				dates[i] = false;
<?php
for($i = 0; $i < $end_of_date+1; $i++) {
	$votecnt[$i] = 0;
	$votedusers[$i] = "";
}
for($i = 0; $row = $sql_voteddata->fetch_array(); $i++) {
	$date = $row['Date_'];
	$votecnt[$date]++;
	printf("/* %s */\n", $row['User']);
	if(strpos($votedusers[$date], $row['User']) === false) {
		$votedusers[$date] .= sprintf("%s<br />", $row['User']);
		printf("// %d|%s\n", $date, $votedusers[$date]);
	}
?>
			voteddata[<?=$i?>] = {
				'date': <?=$row['Date_']?>,
				'user': "<?=$row['User']?>",
				'reason': "<?=$row['Reason']?>"
			};
<?php
}
/*for($i = 0; $i < $end_of_date+1; $i++)
	$votedusers[$i] = substr($votedusers[$i], 0, -1);*/
?>

			$(document).ready(function() {
<?php
if(isset($_SESSION['User'])) {
?>
				step = 2;
				$('div#second_step').css('display', 'block');
<?php
}
else {
?>
				$('div#first_step').css('display', 'block');
<?php
}
?>

				$('button.next').click(function() {
					switch(step) {
						case 1:
							if($('input.name').val().length == 0)
								alert("이름을 입력하세요.");
							else {
								$('input.name').prop('disabled', true);
								$('button.next').prop('disabled', true);

								$('#login').css('display', 'block');
								$('#login').fadeTo('slow', 0.7, function() {
									$.ajax({
										type: "post",
										url: "func/session_login.php",
										cache: false,
										data: {
											Name: $('input.name').val()
										},
										success: function(data) {
											var result = data.split('|');
											if(!result[0])
												alert(result[1]);
											else
												$('#login').fadeTo('slow', 0, function() {
													$('#contents').fadeTo('slow', 0, function() {
														$('#first_step').css('display', 'none');
														$('#second_step').css('display', 'block');

														step++;
														$('#contents').fadeTo('slow', 1, function() { });
													});
												});
										}
									});
								});
							}
							break;
						case 2:
							var post_reason = ($('input.reason').val().length == 0) ? "입력안함" : $('input.reason').val();
							var post_dates = "";

							for(var i = 0; i < <?=$end_of_date+1?>; i++)
								if(dates[i])
									post_dates += "2016-08-" + String("0" + i).slice(-2) + ", ";
							post_dates = post_dates.slice(0, -2);
							if(post_dates == "")
								alert("날짜를 선택하세요.");
							else
								$.ajax({
									type: "post",
									url: "func/input_date.php",
									cache: false,
									data: {
										Dates: post_dates,
										Reason: post_reason
									},
									success: function(data) {
										var result = data.split('|');
										if(!result[0])
											alert(result[1]);
										else
											window.location.reload();
									}
								});
							break;
					}
				});

				$('#second_step>table>tbody>tr>td').click(function() {
					var date = parseInt($(this).text().slice(0, 2));

					if(date > 0 && date <= <?=$end_of_date?>) {
						dates[date] = !dates[date];
						if(dates[date])
							$(this).css('background-color', '#55AA55');
						else
							$(this).css('background-color', '#FFF');

						var datecnt = 0,
							datacnt = 0,
							view_date_html_cnt = 0,
							view_date_html = "",
							input_reason_request;

						for(var i = 0; i < <?=$end_of_date+1?>; i++)
							if(dates[i]) {
								date = i;
								datecnt++;

								datacnt = 0;
								for(var j = 0; j < <?=$num_voteddatas?>; j++)
									if(voteddata[j]['date'] == i) {
										if(datacnt++ == 0) {
											if(view_date_html_cnt != 0) {
												view_date_html += "\t\t\t\t</div>\n";
											}
											view_date_html += "\t\t\t\t<div>\n";
											view_date_html += "\t\t\t\t<p>2016년 8월 " + i + "일</p>\n"
										}
										view_date_html += "\t\t\t\t\t<b>" + voteddata[j]['user'] + "</b>: " + voteddata[j]['reason'];
										if(voteddata[j]['user'] == getUserName()) {
											view_date_html += "&nbsp;<a>삭제</a>";
										}
										view_date_html += "<br />\n";
										view_date_html_cnt++;
									}
							}
							if(view_date_html_cnt != 0) {
								view_date_html += "\t\t\t\t</div>\n";
							}

						if($('#view_date').css('display') != 'none' && (datecnt == 0 || view_date_html_cnt == 0))
							$('#view_date').fadeTo('fast', 0, function() {
								$('#view_date').css('display', 'none');
							});
						if($('#input_reason').css('display') != 'none' && datecnt == 0) {
							$('#input_reason').fadeTo('fast', 0, function() {
								$('#input_reason').css('display', 'none');
							});
							return;
						}
						else if(datecnt == 1)
							input_reason_request = date + "일에 ";
						else
							input_reason_request = "해당 기간(" + datecnt + "일)에 ";
						input_reason_request += "참석 불가능한 사유를 입력하세요.";

						if(view_date_html_cnt != 0) {
							$('#view_date').css('display', 'block');
							$('#view_date').html(view_date_html);
							$('#view_date').fadeTo('slow', 1, function() { });
						}

						$('#input_reason').css('display', 'block');
						$('#input_reason>p:first-child').html(input_reason_request);
						$('#input_reason').fadeTo('slow', 1, function() { });
					}
				});
			});

			function getUserName() {
				var session_name = "<?=$_SESSION['User']?>";

				return (session_name.length != 0) ? session_name : $('input.name').val();
			}
		</script>
	</head>
	<body>
		<div id="contents">
			<div id="first_step" class="step">
				<p class="request">이름을 입력하세요.</p>
				<div class="input">
					<input class="name" type="text" />
					<button class="next">다음</button>
				</div>
			</div>

			<div id="second_step" class="step">
				<p class="request">날짜 또는 기간을 선택하세요.</p>
				<p><b>2016년 8월</b></p>
				<table>
					<tr>
						<td>일</td>
						<td>월</td>
						<td>화</td>
						<td>수</td>
						<td>목</td>
						<td>금</td>
						<td>토</td>
					</tr>
<?php
$date = 0;
for($i = 0; ($i % 7 != 0 || $date < $end_of_date); $i++) {
	if($i % 7 == 0)
		printf("\t\t\t\t\t<tr>\n");
	if($i < $start_of_date || $date >= $end_of_date)
		printf("\t\t\t\t\t\t<td>&nbsp;<br />&nbsp;</td>\n");
	else {
		printf("\t\t\t\t\t\t<td>%02d<br />", ++$date);
		if($votecnt[$date] > 0)
			printf("<span>%s</span></td>\n", $votedusers[$date]);
		else
			printf("&nbsp;</td>\n");
	}
	if($i % 7 == 6)
		printf("\t\t\t\t\t</tr>\n");
}
?>
				</table>
			</div>

			<div id="view_date" class="step">
				&nbsp;
			</div>

			<div id="input_reason" class="step">
				<p class="request">&nbsp;</p>
				<div class="input">
					<input class="reason" type="text" />
					<button class="next">다음</button>
				</div>
			</div>

			<div id="credit">
				<p>Copyright ⓒ2016 서성범. All rights reserved.</p>
			</div>
		</div>

		<div id="login">
			<div id="login_msg">
				세션 로그인 처리중입니다.
			</div>
		</div>
	</body>
</html>