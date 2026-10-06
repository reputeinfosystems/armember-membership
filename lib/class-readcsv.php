<?php
/**
 * Use this to read CSV files.
 * PHP's fgetcsv() does not conform to RFC 4180.
 * In particular, it doesn't handle the correct quote escaping syntax.
 */
class ReadCSV {

	const field_start    = 0;
	const unquoted_field = 1;
	const quoted_field   = 2;
	const found_quote    = 3;
	const found_cr_q     = 4;
	const found_cr       = 5;
	var $is_file;
	private $file;
	private $file_contents;
	private $file_position;
	private $sep;
	// If $eof is TRUE, the next next_char() will return FALSE.
	// Note that this is different to feof(), which is TRUE
	// _after_ EOF is encountered.
	private $eof;
	private $nc;
	/**
	 * @param $file_handle
	 *  open file to read from
	 * @param $skip
	 *  initial character sequence to skip if found. e.g. UTF-8 byte-order mark
	 */
	public function __construct( $filename, $skip = "\xEF\xBB\xBF" ) {
		$this->is_file = false;
		if ( ! empty( $filename ) ) {
			$this->file  = $filename;
			$this->sep   = self::get_separator( $filename );

			global $wp_filesystem;

			if ( ! $wp_filesystem ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				WP_Filesystem();
			}

			$file_contents = $wp_filesystem->get_contents( $filename );

			if ( false !== $file_contents ) {
				$this->is_file       = true;
				$this->file_contents = $file_contents;
				$this->file_position = 0;
				$this->nc            = isset( $this->file_contents[0] ) ? $this->file_contents[0] : false;

				// skip junk at start
				for ( $i = 0; $i < strlen( $skip ); $i++ ) {
					if ( $this->nc !== $skip[ $i ] ) {
						break;
					}
					$this->file_position++;
					$this->nc = isset( $this->file_contents[ $this->file_position ] ) ? $this->file_contents[ $this->file_position ] : false;
				}
				$this->eof = ( $this->nc === false );
			}
		}
	}
	public function closeFilePointer() {
		$this->file_contents = '';
		$this->file_position = 0;
		$this->nc             = false;
		$this->eof            = true;
	}
	public function get_data() {
		$first = true;
		$i     = 0;
		$data  = array();
		while ( ( $line = $this->get_single_row() ) !== null ) {
			// If the first line is empty, abort
			// If another line is empty, just skip it
			if ( empty( $line ) ) {
				if ( $first ) {
					break;
				} else {
					continue;
				}
			}
			// If we are on the first line, the columns are the headers
			if ( $first ) {
				$headers = $line;
				$first   = false;
				continue;
			}
			// Separate user data from meta
			foreach ( $line as $ckey => $column ) {
				$column_name                = $headers[ $ckey ];
				$column                     = trim( $column );
				$data[ $i ][ $column_name ] = $column;
			}
			$i++;
		}

		return $data;
	}
	public function get_single_row() {
		if ( $this->eof ) {
			return null;
		}

		$row   = array();
		$field = '';
		$state = self::field_start;

		while ( 1 ) {
			$char = $this->next_char();
			if ( $state == self::quoted_field ) {
				if ( $char === false ) {
					// EOF. (TODO: error case - no closing quote)
					$row[] = $field;
					return $row;
				}
				// Fall through to accumulate quoted chars in switch() {...}
			} elseif ( $char === false || $char == "\n" ) {
				// End of record.
				// (TODO: error case if $state==self::field_start here - trailing comma)
				$row[] = $field;
				return $row;
			} elseif ( $char == "\r" ) {
				// Possible start of \r\n line end, but might be just part of foo\rbar
				$state = ( $state == self::found_quote ) ? self::found_cr_q : self::found_cr;
				continue;
			} elseif ( $char == $this->sep &&
				( $state == self::field_start ||
				$state == self::found_quote ||
				$state == self::unquoted_field ) ) {
				// End of current field, start of next field
				$row[] = $field;
				$field = '';
				$state = self::field_start;
				continue;
			}

			switch ( $state ) {

				case self::field_start:
					if ( $char == '"' ) {
						$state = self::quoted_field;
					} else {
						$state  = self::unquoted_field;
						$field .= $char;
					}
					break;

				case self::quoted_field:
					if ( $char == '"' ) {
						$state = self::found_quote;
					} else {
						$field .= $char;
					}
					break;

				case self::unquoted_field:
					$field .= $char;
					// (TODO: error case if '"' in middle of unquoted field)
					break;

				case self::found_quote:
					// Found '"' escape sequence
					$field .= $char;
					$state  = self::quoted_field;
					// (TODO: error case if $char!='"' - non-separator char after single quote)
					break;

				case self::found_cr:
					// Lone \rX instead of \r\n. Treat as literal \rX. (TODO: error case?)
					$field .= "\r" . $char;
					$state  = self::unquoted_field;
					break;

				case self::found_cr_q:
					// (TODO: error case: "foo"\rX instead of "foo"\r\n or "foo"\n)
					$field .= "\r" . $char;
					$state  = self::quoted_field;
					break;
			}
		}
	}
	public function next_char() {
		$c         = $this->nc;
		$this->file_position++;
		$this->nc  = isset( $this->file_contents[ $this->file_position ] ) ? $this->file_contents[ $this->file_position ] : false;
		$this->eof = ( $this->nc === false );
		return $c;
	}
	public function get_separator( $file ) {
		$file_detail = self::analyse_file( $file );
		$separator   = $file_detail['delimiter']['value'];
		return $separator;
	}
	public function analyse_file( $file, $capture_limit_in_kb = 100 ) {
		 // capture starting memory usage
		$output['peak_mem']['start'] = memory_get_peak_usage( true );
		// log the limit how much of the file was sampled (in Kb)
		$output['read_kb'] = $capture_limit_in_kb;
		// read in file
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$contents = $wp_filesystem->get_contents( $file );

		if ( false !== $contents ) {
			$contents = substr( $contents, 0, $capture_limit_in_kb * 1024 );
		} else {
			$contents = '';
		}
		// specify allowed field delimiters
		$delimiters = array(
			'comma'     => ',',
			'semicolon' => ';',
			'tab'       => "\t",
			'pipe'      => '|',
			'colon'     => ':',
		);
		// specify allowed line endings
		$line_endings = array(
			'rn' => "\r\n",
			'n'  => "\n",
			'r'  => "\r",
			'nr' => "\n\r",
		);
		// loop and count each line ending instance
		foreach ( $line_endings as $key => $value ) {
			$line_result[ $key ] = substr_count( $contents, $value );
		}
		// sort by largest array value
		asort( $line_result );
		// log to output array
		$output['line_ending']['results'] = $line_result;
		$output['line_ending']['count']   = end( $line_result );
		$output['line_ending']['key']     = key( $line_result );
		$output['line_ending']['value']   = $line_endings[ $output['line_ending']['key'] ];
		$lines                            = explode( $output['line_ending']['value'], $contents );
		// remove last line of array, as this maybe incomplete?
		array_pop( $lines );
		// create a string from the legal lines
		$complete_lines = implode( ' ', $lines );
		// log statistics to output array
		$output['lines']['count']  = count( $lines );
		$output['lines']['length'] = strlen( $complete_lines );
		// loop and count each delimiter instance
		foreach ( $delimiters as $delimiter_key => $delimiter ) {
			$delimiter_result[ $delimiter_key ] = substr_count( $complete_lines, $delimiter );
		}
		// sort by largest array value
		asort( $delimiter_result );
		// log statistics to output array with largest counts as the value
		$output['delimiter']['results'] = $delimiter_result;
		$output['delimiter']['count']   = end( $delimiter_result );
		$output['delimiter']['key']     = key( $delimiter_result );
		$output['delimiter']['value']   = $delimiters[ $output['delimiter']['key'] ];
		// capture ending memory usage
		$output['peak_mem']['end'] = memory_get_peak_usage( true );
		return $output;
	}
}
