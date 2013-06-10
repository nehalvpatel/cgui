<?php

	$timezone = "America/Chicago";
	
	$wallet = array(
		"Name" => "",
		"Address" => "",
		"Currency" => ""
	);
	
	if (empty($wallet["Address"])) {
		die("You didn't configure the wallet address.");
	}
	
	if ($wallet["Currency"] == "LTC") {
		$wallet_data = json_decode(file_get_contents("http://api.ltcd.info/address/" . $wallet["Address"]), true);
		if (isset($wallet_data["error"])) {
			die("Error fetching wallet data: " . $wallet_data["error"]);
		}
		$transactions_key = "transactions";
		$transactions_count = $wallet_data["txin"] + $wallet_data["txout"];
		$block_key = "block";
		$time_key = "timestamp";
		$hash_url = "http://explorer.litecoin.net/tx/";
		$amount_key = "amount";
		$balance_key = "balance";
	} elseif ($wallet["Currency"] == "BTC") {
		$wallet_data = json_decode(@file_get_contents("http://blockchain.info/address/" . $wallet["Address"] . "?format=json"), true);
		if (!$wallet_data) {
			die("Error fetching wallet data");
		}
		$transactions_key = "txs";
		$transactions_count = $wallet_data["n_tx"];
		$block_key = "block_height";
		$time_key = "time";
		$hash_url = "http://blockchain.info/tx/";
		$amount_key = "result";
		$balance_key = "final_balance";
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
		<link rel="apple-touch-icon-precomposed" sizes="57x57" href="touch-icon-iphone-114.png">
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="touch-icon-iphone-114.png">
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="touch-icon-ipad-144.png">
		<link rel="apple-touch-icon-precomposed" sizes="144x144" href="touch-icon-ipad-144.png">
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
				<strong>Balance:</strong> <?php if ($wallet["Currency"] == "LTC") { echo $wallet_data[$balance_key] . " " . $wallet["Currency"];  } else { echo number_format($wallet_data[$balance_key] / 100000000, 8) . " " . $wallet["Currency"]; } echo PHP_EOL; ?>
				<br>
				<strong>Transactions:</strong> <?php echo $transactions_count; ?>
			</div>
			<?php if (count($wallet_data[$transactions_key]) > 0) { ?>
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
						
						function sort_chronologically($a, $b) {
							global $time_key;
							return $b[$time_key] - $a[$time_key];
						}
						usort($wallet_data[$transactions_key], "sort_chronologically");
						
						foreach ($wallet_data[$transactions_key] as $id => $transaction) {
							if ($wallet["Currency"] == "LTC") {
								$amount = $transaction["amount"];
							} elseif ($wallet["Currency"] == "BTC") {
								$sent = 0;
								foreach ($transaction["inputs"] as $input) {
									if ($input["prev_out"]["addr"] == $wallet["Address"]) {
										$sent += $input["prev_out"]["value"];
									}
								}
								
								$received = 0;
								foreach ($transaction["out"] as $input) {
									if ($input["addr"] == $wallet["Address"]) {
										$received += $input["value"];
									}
								}
								
								if ($sent == 0) {
									$amount = $received / 100000000;
								} elseif ($received == 0) {
									$amount = $sent / 100000000;
									$amount = 0 - $amount;
								}
								$amount = number_format($amount, 8);
							}
					?>
					<tr class="<?php if ($amount >= 0) { echo "success"; } else { echo "error"; } ?>">
						<td data-title="ID"><?php echo $transactions_count - $id; ?></td>
						<td data-title="Transaction" class="break-word"><a href="<?php echo $hash_url . $transaction["hash"]; ?>"><?php echo $transaction["hash"]; ?></a></td>
						<td data-title="Block"><?php echo $transaction[$block_key]; ?></td>
						<td data-title="Time"><?php $date_timezone = new DateTime(date("Y-m-d H:i:s", $transaction[$time_key]), new DateTimeZone("UTC")); $date_timezone->setTimezone(new DateTimeZone($timezone)); echo $date_timezone->format('M j, Y h:i:s A'); ?></td>
						<td data-title="Amount"><?php echo $amount; ?></td>
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