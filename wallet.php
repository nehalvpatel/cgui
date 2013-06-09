<?php

	$timezone = "America/Chicago";
	
	$wallet = array(
		"Name" => "",
		"Address" => ""
	);
	
	if (empty($wallet["Address"])) {
		die("You didn't configure the wallet address.");
	}
	
	$wallet_data = json_decode(file_get_contents("http://api.ltcd.info/address/" . $wallet["Address"]), true);
	
	if (isset($wallet_data["error"])) {
		die("Error fetching wallet data: " . $wallet_data["error"]);
	}
	
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta name="author" content="Nehal Patel">
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<link href="css/bootstrap-responsive.min.css" rel="stylesheet">
		<link href="css/main.css" rel="stylesheet">
		<link rel="apple-touch-icon" sizes="57x57" href="touch-icon-iphone-114.png">
		<link rel="apple-touch-icon" sizes="114x114" href="touch-icon-iphone-114.png">
		<link rel="apple-touch-icon" sizes="72x72" href="touch-icon-ipad-144.png">
		<link rel="apple-touch-icon" sizes="144x144" href="touch-icon-ipad-144.png">
		<link rel="icon" href="favicon.png">
		<!--[if IE]><link rel="shortcut icon" href="favicon.ico"><![endif]-->
		<title><?php if (isset($wallet["Name"]) && !empty($wallet["Name"])) { echo $wallet["Name"]; } else { echo $wallet["Address"]; } ?></title>
	</head>
	<body>
		<div class="container" id="no-more-tables">
			<h1 class="info-header">Stats</h1>
			<div class="well well-small info-block">
				<strong>Address:</strong> <span class="break-word"><?php echo $wallet["Address"]; ?></span><?php echo PHP_EOL; ?>
				<hr style="margin-top: 5px; margin-bottom: 5px;">
				<strong>Balance:</strong> <?php echo $wallet_data["balance"] . " LTC" . PHP_EOL; ?>
				<br>
				<strong>Transactions:</strong> <?php echo $wallet_data["txin"] . " in, " . $wallet_data["txout"] . " out"; ?>
			</div>
			<?php if (count($wallet_data["transactions"]) > 0) { ?>
			<h1 class="info-header">Transactions</h1>
			<table class="table table-striped table-bordered table-hover info-block">
				<thead>
					<tr>
						<th>ID</th>
						<th>Transaction</th>
						<th>Block</th>
						<th>Time</th>
						<th>Amount</th>
					</tr>
				</thead>
				<tbody>
<?php
						function cmp($a, $b) {
								return $b["timestamp"] - $a["timestamp"];
						}
						usort($wallet_data["transactions"], "cmp");

						foreach ($wallet_data["transactions"] as $id => $transaction) {
					?>
					<tr class="<?php if ($transaction["amount"] >= 0) { echo "success"; } else { echo "error"; } ?>">
						<td data-title="ID"><?php echo count($wallet_data["transactions"]) - $id; ?></td>
						<td data-title="Transaction" class="break-word"><a href="http://explorer.litecoin.net/tx/<?php echo $transaction["hash"]; ?>"><?php echo $transaction["hash"]; ?></a></td>
						<td data-title="Block"><?php echo $transaction["block"]; ?></td>
						<td data-title="Time"><?php $date_timezone = new DateTime(date("Y-m-d H:i:s", $transaction["timestamp"]), new DateTimeZone("UTC")); $date_timezone->setTimezone(new DateTimeZone($timezone)); echo $date_timezone->format('M j, Y h:i:s A'); ?></td>
						<td data-title="Amount"><?php echo $transaction["amount"]; ?></td>
					</tr>
<?php
						}
					?>
				</tbody>
			</table>
<?php } ?>
			<footer>
				<div style="text-align: center; margin-bottom: 20px;"><a href="http://www.itspatel.com/">itsPATEL.com</a></div>
			</footer>
		</div>
		<script src="http://code.jquery.com/jquery-latest.js"></script>
		<script src="js/bootstrap.min.js"></script>
	</body>
</html>