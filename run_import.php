<?php

	class run_import
	{
		private $url = 'https://evidujem.sk/';
		private $file_name = __DIR__ . '/zadanie.xml';
		public $count, $html, $text, $date;


        function __construct () {
            $this->checkUrl();
            $this->curl();
            $this->getCount();
            $this->date = date("Y-m-d");
            $this->getText();
            $this->saveFile();
            echo 'Import data is done.';
		}

        /**
         * Simple call checker
         */
        private function checkUrl() {
            $params = $_SERVER['QUERY_STRING'];
            if ( $params !== '2020evidujem' ){
                header("HTTP/1.0 404 Not Found");
                die;
            }
        }

        /**
         * Curl get html data from page
         */
        private function curl() {
			$c = curl_init($this->url);

			curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($c,CURLOPT_HEADER,0);

			$this->html = curl_exec($c);

			if (curl_error($c))
				die(curl_error($c));

			// Get the status code
			$status = curl_getinfo($c, CURLINFO_HTTP_CODE);

			curl_close($c);
		}

		public function getCount() {
			if ( $this->html == '' )
				die('Wrapped Html is empty');

            preg_match("%<div class=\"span6 lead text-center\">(.*?)</div>%i", $this->html, $matches);
            $this->count = preg_replace('/[^0-9]/', '', $matches[1]);

            if ( !is_numeric( $this->count ) )
                die('Preg match return bad string');
		}

        /**
         * getText function check if file exist than call function newFile or editFile
         */
        public function getText(){
            if ( file_exists( $this->file_name ) )
                $this->editFile();
            else
                $this->newFile();
        }

		public function newFile () {
            $this->text = '<data>'. "\n\n";
            $this->text .= '<set><date>' . $this->date . '</date><count>' . $this->count . '</count></set>'. "\n\n";
            $this->text .= '</data>';
        }

        /**
         * This function must be updated with date controll if exist
         */
        public function editFile () {
            $text = file_get_contents( $this->file_name );
            $new_text = '<set><date>' . $this->date . '</date><count>' . $this->count . '</count></set>'. "\n\n";
            $new_text .= '</data>';
            $this->text = str_replace( '</data>', $new_text, $text );
        }

        public function saveFile(){
            file_put_contents( $this->file_name, $this->text );
        }
	}