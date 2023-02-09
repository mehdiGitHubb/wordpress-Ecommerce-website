<?php
namespace AIOSEO\Plugin\Common\Standalone;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the headline analysis.
 *
 * @since 4.1.2
 */
class HeadlineAnalyzer {
	/**
	 * Class constructor.
	 *
	 * @since 4.1.2
	 */
	public function __construct() {
		if ( ! is_admin() || wp_doing_cron() ) {
			return;
		}

		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue' ] );

		if ( ! aioseo()->options->advanced->headlineAnalyzer ) {
			return;
		}

		add_filter( 'monsterinsights_headline_analyzer_enabled', '__return_false' );
		add_filter( 'exactmetrics_headline_analyzer_enabled', '__return_false' );
	}

	/**
	 * Enqueues the headline analyzer.
	 *
	 * @since 4.1.2
	 *
	 * @return void
	 */
	public function enqueue() {
		global $wp_version;
		if (
			! aioseo()->helpers->isScreenBase( 'post' ) ||
			version_compare( $wp_version, '5.2', '<' ) ||
			! aioseo()->access->hasCapability( 'aioseo_page_analysis' )
		) {
			return;
		}

		if ( ! aioseo()->options->advanced->headlineAnalyzer ) {
			return;
		}

		$path = '/vendor/jwhennessey/phpinsight/autoload.php';
		if ( ! aioseo()->core->fs->exists( AIOSEO_DIR . $path ) ) {
			return;
		}
		require AIOSEO_DIR . $path;

		aioseo()->core->assets->load( 'src/react/headline-analyzer/main.js' );
	}

	/**
	 * Returns the result of the analsyis.
	 *
	 * @since 4.1.2
	 *
	 * @param  string $title The title.
	 * @return array         The result.
	 */
	public function getResult( $title ) {
		$result = $this->getHeadlineScore( $title );

		if ( ! empty( $result->err ) ) {
			return false;
		}

		return [
			'result'   => $result,
			'analysed' => ! $result->err,
			'sentence' => ucwords( wp_unslash( sanitize_text_field( $title ) ) ),
			'score'    => ! empty( $result->score ) ? $result->score : 0
		];
	}

	/**
	 * Returns the score.
	 *
	 * @since 4.1.2
	 *
	 * @param  string    $title The title.
	 * @return \stdClass        The result.
	 */
	public function getHeadlineScore( $title ) {
		$result                           = new \stdClass();
		$result->originalExplodedHeadline = explode( ' ', wp_unslash( $title ) );

		// Strip useless characters and whitespace.
		$title = preg_replace( '/[^A-Za-z0-9 ]/', '', $title );
		$title = preg_replace( '!\s+!', ' ', $title );
		$title = strtolower( $title );

		$result->input = $title;

		// If the headline is invalid, return an error.
		if ( ! $title || ' ' === $title || trim( $title ) === '' ) {
			$result->err = true;
			$result->msg = 'The headline is invalid.';

			return $result;
		}

		$totalScore               = 0;
		$explodedHeadline         = explode( ' ', $title );
		$result->explodedHeadline = $explodedHeadline;
		$result->err              = false;

		// The optimal length is 55 characters.
		$result->length = strlen( str_replace( ' ', '', $title ) );
		$totalScore     = $totalScore + 3;

		//phpcs:disable Squiz.ControlStructures.ControlSignature
		if ( $result->length <= 19 ) { $totalScore += 5; }
		elseif ( $result->length >= 20 && $result->length <= 34 ) { $totalScore += 8; }
		elseif ( $result->length >= 35 && $result->length <= 66 ) { $totalScore += 11; }
		elseif ( $result->length >= 67 && $result->length <= 79 ) { $totalScore += 8; }
		elseif ( $result->length >= 80 ) { $totalScore += 5; }

		// The average headline is 6-7 words long.
		$result->wordCount = count( $explodedHeadline );
		$totalScore        = $totalScore + 3;

		if ( 0 === $result->wordCount ) { $totalScore = 0; }
		elseif ( $result->wordCount >= 2 && $result->wordCount <= 4 ) { $totalScore += 5; }
		elseif ( $result->wordCount >= 5 && $result->wordCount <= 9 ) { $totalScore += 11; }
		elseif ( $result->wordCount >= 10 && $result->wordCount <= 11 ) { $totalScore += 8; }
		elseif ( $result->wordCount >= 12 ) { $totalScore += 5; }

		// Check for power words, emotional words, etc.
		$result->powerWords               = $this->matchWords( $result->input, $result->explodedHeadline, $this->powerWords() );
		$result->powerWordsPercentage     = count( $result->powerWords ) / $result->wordCount;
		$result->emotionWords             = $this->matchWords( $result->input, $result->explodedHeadline, $this->emotionPowerWords() );
		$result->emotionalWordsPercentage = count( $result->emotionWords ) / $result->wordCount;
		$result->commonWords              = $this->matchWords( $result->input, $result->explodedHeadline, $this->commonWords() );
		$result->commonWordsPercentage    = count( $result->commonWords ) / $result->wordCount;
		$result->uncommonWords            = $this->matchWords( $result->input, $result->explodedHeadline, $this->uncommonWords() );
		$result->uncommonWordsPercentage  = count( $result->uncommonWords ) / $result->wordCount;
		$result->detectedWordTypes        = [];

		if ( $result->emotionalWordsPercentage < 0.1 ) {
			$result->detectedWordTypes[] = __( 'emotion', 'all-in-one-seo-pack' );
		} else {
			$totalScore = $totalScore + 15;
		}

		if ( $result->commonWordsPercentage < 0.2 ) {
			$result->detectedWordTypes[] = __( 'common', 'all-in-one-seo-pack' );
		} else {
			$totalScore = $totalScore + 11;
		}

		if ( $result->uncommonWordsPercentage < 0.1 ) {
			$result->detectedWordTypes[] = __( 'uncommon', 'all-in-one-seo-pack' );
		} else {
			$totalScore = $totalScore + 15;
		}

		if ( count( $result->powerWords ) < 1 ) {
			$result->detectedWordTypes[] = __( 'power', 'all-in-one-seo-pack' );
		} else {
			$totalScore = $totalScore + 19;
		}

		if (
			$result->emotionalWordsPercentage >= 0.1 &&
			$result->commonWordsPercentage >= 0.2 &&
			$result->uncommonWordsPercentage >= 0.1 &&
			count( $result->powerWords ) >= 1
		) {
			$totalScore = $totalScore + 3;
		}

		$sentiment         = new \PHPInsight\Sentiment();
		$sentimentClass    = $sentiment->categorise( $title );
		$result->sentiment = $sentimentClass;

		$totalScore = $totalScore + ( 'pos' === $result->sentiment ? 10 : ( 'neg' === $result->sentiment ? 10 : 7 ) );

		$headlineTypes = [];
		if ( strpos( $title, __( 'how to', 'all-in-one-seo-pack' ) ) !== false || strpos( $title, __( 'howto', 'all-in-one-seo-pack' ) ) !== false ) {
			$headlineTypes[] = __( 'How-To', 'all-in-one-seo-pack' );
			$totalScore      = $totalScore + 7;
		}

		$listWords = array_intersect( $explodedHeadline, $this->numericalIndicators() );
		if ( preg_match( '~[0-9]+~', $title ) || ! empty( $listWords ) ) {
			$headlineTypes[] = __( 'List', 'all-in-one-seo-pack' );
			$totalScore      = $totalScore + 7;
		}

		if ( in_array( $explodedHeadline[0], $this->primaryQuestionIndicators(), true ) ) {
			if ( in_array( $explodedHeadline[1], $this->secondaryQuestionIndicators(), true ) ) {
				$headlineTypes[] = __( 'Question', 'all-in-one-seo-pack' );
				$totalScore      = $totalScore + 7;
			}
		}

		if ( empty( $headlineTypes ) ) {
			$headlineTypes[] = __( 'General', 'all-in-one-seo-pack' );
			$totalScore      = $totalScore + 5;
		}

		$result->headlineTypes = $headlineTypes;
		$result->score         = $totalScore >= 93 ? 93 : $totalScore;

		return $result;
	}

	/**
	* Tries to find matches for power words, emotional words, etc. in the headline.
	*
	* @since 4.1.2
	*
	* @param  string $sentence         The headline.
	* @param  array  $explodedHeadline The exploded headline.
	* @return array                    The matches that were found.
	*/
	public function matchWords( $headline, $explodedHeadline, $words ) {
		$foundMatches = [];
		foreach ( $words as $word ) {
			$strippedWord = preg_replace( '/[^A-Za-z0-9 ]/', '', $word );

			// Check if word is a phrase.
			if ( strpos( $word, ' ' ) !== false ) {
				if ( strpos( $headline, $strippedWord ) !== false ) {
					$foundMatches[] = $word;
				}
				continue;
			}
			// Check if it is a single word.
			if ( in_array( $strippedWord, $explodedHeadline, true ) ) {
				$foundMatches[] = $word;
			}
		}

		return $foundMatches;
	}

	/**
	 * Returns a list of numerical indicators.
	 *
	 * @since 4.1.2
	 *
	 * @return array The list of numerical indicators.
	 */
	private function numericalIndicators() {
		return [
			__( 'one', 'all-in-one-seo-pack' ),
			__( 'two', 'all-in-one-seo-pack' ),
			__( 'three', 'all-in-one-seo-pack' ),
			__( 'four', 'all-in-one-seo-pack' ),
			__( 'five', 'all-in-one-seo-pack' ),
			__( 'six', 'all-in-one-seo-pack' ),
			__( 'seven', 'all-in-one-seo-pack' ),
			__( 'eight', 'all-in-one-seo-pack' ),
			__( 'nine', 'all-in-one-seo-pack' ),
			__( 'eleven', 'all-in-one-seo-pack' ),
			__( 'twelve', 'all-in-one-seo-pack' ),
			__( 'thirt', 'all-in-one-seo-pack' ),
			__( 'fift', 'all-in-one-seo-pack' ),
			__( 'hundred', 'all-in-one-seo-pack' ),
			__( 'thousand', 'all-in-one-seo-pack' ),
		];
	}

	/**
	 * Returns a list of primary question indicators.
	 *
	 * @since 4.1.2
	 *
	 * @return array The list of primary question indicators.
	 */
	private function primaryQuestionIndicators() {
		return [
			__( 'where', 'all-in-one-seo-pack' ),
			__( 'when', 'all-in-one-seo-pack' ),
			__( 'how', 'all-in-one-seo-pack' ),
			__( 'what', 'all-in-one-seo-pack' ),
			__( 'have', 'all-in-one-seo-pack' ),
			__( 'has', 'all-in-one-seo-pack' ),
			__( 'does', 'all-in-one-seo-pack' ),
			__( 'do', 'all-in-one-seo-pack' ),
			__( 'can', 'all-in-one-seo-pack' ),
			__( 'are', 'all-in-one-seo-pack' ),
			__( 'will', 'all-in-one-seo-pack' ),
		];
	}

	/**
	 * Returns a list of secondary question indicators.
	 *
	 * @since 4.1.2
	 *
	 * @return array The list of secondary question indicators.
	 */
	private function secondaryQuestionIndicators() {
		return [
			__( 'you', 'all-in-one-seo-pack' ),
			__( 'they', 'all-in-one-seo-pack' ),
			__( 'he', 'all-in-one-seo-pack' ),
			__( 'she', 'all-in-one-seo-pack' ),
			__( 'your', 'all-in-one-seo-pack' ),
			__( 'it', 'all-in-one-seo-pack' ),
			__( 'they', 'all-in-one-seo-pack' ),
			__( 'my', 'all-in-one-seo-pack' ),
			__( 'have', 'all-in-one-seo-pack' ),
			__( 'has', 'all-in-one-seo-pack' ),
			__( 'does', 'all-in-one-seo-pack' ),
			__( 'do', 'all-in-one-seo-pack' ),
			__( 'can', 'all-in-one-seo-pack' ),
			__( 'are', 'all-in-one-seo-pack' ),
			__( 'will', 'all-in-one-seo-pack' ),
		];
	}

	/**
	 * Returns a list of power words.
	 *
	 * @since 4.1.2
	 *
	 * @return array The list of power words.
	 */
	private function powerWords() {
		return [
			__( 'great', 'all-in-one-seo-pack' ),
			__( 'free', 'all-in-one-seo-pack' ),
			__( 'focus', 'all-in-one-seo-pack' ),
			__( 'remarkable', 'all-in-one-seo-pack' ),
			__( 'confidential', 'all-in-one-seo-pack' ),
			__( 'sale', 'all-in-one-seo-pack' ),
			__( 'wanted', 'all-in-one-seo-pack' ),
			__( 'obsession', 'all-in-one-seo-pack' ),
			__( 'sizable', 'all-in-one-seo-pack' ),
			__( 'new', 'all-in-one-seo-pack' ),
			__( 'absolutely lowest', 'all-in-one-seo-pack' ),
			__( 'surging', 'all-in-one-seo-pack' ),
			__( 'wonderful', 'all-in-one-seo-pack' ),
			__( 'professional', 'all-in-one-seo-pack' ),
			__( 'interesting', 'all-in-one-seo-pack' ),
			__( 'revisited', 'all-in-one-seo-pack' ),
			__( 'delivered', 'all-in-one-seo-pack' ),
			__( 'guaranteed', 'all-in-one-seo-pack' ),
			__( 'challenge', 'all-in-one-seo-pack' ),
			__( 'unique', 'all-in-one-seo-pack' ),
			__( 'secrets', 'all-in-one-seo-pack' ),
			__( 'special', 'all-in-one-seo-pack' ),
			__( 'lifetime', 'all-in-one-seo-pack' ),
			__( 'bargain', 'all-in-one-seo-pack' ),
			__( 'scarce', 'all-in-one-seo-pack' ),
			__( 'tested', 'all-in-one-seo-pack' ),
			__( 'highest', 'all-in-one-seo-pack' ),
			__( 'hurry', 'all-in-one-seo-pack' ),
			__( 'alert famous', 'all-in-one-seo-pack' ),
			__( 'improved', 'all-in-one-seo-pack' ),
			__( 'expert', 'all-in-one-seo-pack' ),
			__( 'daring', 'all-in-one-seo-pack' ),
			__( 'strong', 'all-in-one-seo-pack' ),
			__( 'immediately', 'all-in-one-seo-pack' ),
			__( 'advice', 'all-in-one-seo-pack' ),
			__( 'pioneering', 'all-in-one-seo-pack' ),
			__( 'unusual', 'all-in-one-seo-pack' ),
			__( 'limited', 'all-in-one-seo-pack' ),
			__( 'the truth about', 'all-in-one-seo-pack' ),
			__( 'destiny', 'all-in-one-seo-pack' ),
			__( 'outstanding', 'all-in-one-seo-pack' ),
			__( 'simplistic', 'all-in-one-seo-pack' ),
			__( 'compare', 'all-in-one-seo-pack' ),
			__( 'unsurpassed', 'all-in-one-seo-pack' ),
			__( 'energy', 'all-in-one-seo-pack' ),
			__( 'powerful', 'all-in-one-seo-pack' ),
			__( 'colorful', 'all-in-one-seo-pack' ),
			__( 'genuine', 'all-in-one-seo-pack' ),
			__( 'instructive', 'all-in-one-seo-pack' ),
			__( 'big', 'all-in-one-seo-pack' ),
			__( 'affordable', 'all-in-one-seo-pack' ),
			__( 'informative', 'all-in-one-seo-pack' ),
			__( 'liberal', 'all-in-one-seo-pack' ),
			__( 'popular', 'all-in-one-seo-pack' ),
			__( 'ultimate', 'all-in-one-seo-pack' ),
			__( 'mainstream', 'all-in-one-seo-pack' ),
			__( 'rare', 'all-in-one-seo-pack' ),
			__( 'exclusive', 'all-in-one-seo-pack' ),
			__( 'willpower', 'all-in-one-seo-pack' ),
			__( 'complete', 'all-in-one-seo-pack' ),
			__( 'edge', 'all-in-one-seo-pack' ),
			__( 'valuable', 'all-in-one-seo-pack' ),
			__( 'attractive', 'all-in-one-seo-pack' ),
			__( 'last chance', 'all-in-one-seo-pack' ),
			__( 'superior', 'all-in-one-seo-pack' ),
			__( 'how to', 'all-in-one-seo-pack' ),
			__( 'easily', 'all-in-one-seo-pack' ),
			__( 'exploit', 'all-in-one-seo-pack' ),
			__( 'unparalleled', 'all-in-one-seo-pack' ),
			__( 'endorsed', 'all-in-one-seo-pack' ),
			__( 'approved', 'all-in-one-seo-pack' ),
			__( 'quality', 'all-in-one-seo-pack' ),
			__( 'fascinating', 'all-in-one-seo-pack' ),
			__( 'unlimited', 'all-in-one-seo-pack' ),
			__( 'competitive', 'all-in-one-seo-pack' ),
			__( 'gigantic', 'all-in-one-seo-pack' ),
			__( 'compromise', 'all-in-one-seo-pack' ),
			__( 'discount', 'all-in-one-seo-pack' ),
			__( 'full', 'all-in-one-seo-pack' ),
			__( 'love', 'all-in-one-seo-pack' ),
			__( 'odd', 'all-in-one-seo-pack' ),
			__( 'fundamentals', 'all-in-one-seo-pack' ),
			__( 'mammoth', 'all-in-one-seo-pack' ),
			__( 'lavishly', 'all-in-one-seo-pack' ),
			__( 'bottom line', 'all-in-one-seo-pack' ),
			__( 'under priced', 'all-in-one-seo-pack' ),
			__( 'innovative', 'all-in-one-seo-pack' ),
			__( 'reliable', 'all-in-one-seo-pack' ),
			__( 'zinger', 'all-in-one-seo-pack' ),
			__( 'suddenly', 'all-in-one-seo-pack' ),
			__( 'it\'s here', 'all-in-one-seo-pack' ),
			__( 'terrific', 'all-in-one-seo-pack' ),
			__( 'simplified', 'all-in-one-seo-pack' ),
			__( 'perspective', 'all-in-one-seo-pack' ),
			__( 'just arrived', 'all-in-one-seo-pack' ),
			__( 'breakthrough', 'all-in-one-seo-pack' ),
			__( 'tremendous', 'all-in-one-seo-pack' ),
			__( 'launching', 'all-in-one-seo-pack' ),
			__( 'sure fire', 'all-in-one-seo-pack' ),
			__( 'emerging', 'all-in-one-seo-pack' ),
			__( 'helpful', 'all-in-one-seo-pack' ),
			__( 'skill', 'all-in-one-seo-pack' ),
			__( 'soar', 'all-in-one-seo-pack' ),
			__( 'profitable', 'all-in-one-seo-pack' ),
			__( 'special offer', 'all-in-one-seo-pack' ),
			__( 'reduced', 'all-in-one-seo-pack' ),
			__( 'beautiful', 'all-in-one-seo-pack' ),
			__( 'sampler', 'all-in-one-seo-pack' ),
			__( 'technology', 'all-in-one-seo-pack' ),
			__( 'better', 'all-in-one-seo-pack' ),
			__( 'crammed', 'all-in-one-seo-pack' ),
			__( 'noted', 'all-in-one-seo-pack' ),
			__( 'selected', 'all-in-one-seo-pack' ),
			__( 'shrewd', 'all-in-one-seo-pack' ),
			__( 'growth', 'all-in-one-seo-pack' ),
			__( 'luxury', 'all-in-one-seo-pack' ),
			__( 'sturdy', 'all-in-one-seo-pack' ),
			__( 'enormous', 'all-in-one-seo-pack' ),
			__( 'promising', 'all-in-one-seo-pack' ),
			__( 'unconditional', 'all-in-one-seo-pack' ),
			__( 'wealth', 'all-in-one-seo-pack' ),
			__( 'spotlight', 'all-in-one-seo-pack' ),
			__( 'astonishing', 'all-in-one-seo-pack' ),
			__( 'timely', 'all-in-one-seo-pack' ),
			__( 'successful', 'all-in-one-seo-pack' ),
			__( 'useful', 'all-in-one-seo-pack' ),
			__( 'imagination', 'all-in-one-seo-pack' ),
			__( 'bonanza', 'all-in-one-seo-pack' ),
			__( 'opportunities', 'all-in-one-seo-pack' ),
			__( 'survival', 'all-in-one-seo-pack' ),
			__( 'greatest', 'all-in-one-seo-pack' ),
			__( 'security', 'all-in-one-seo-pack' ),
			__( 'last minute', 'all-in-one-seo-pack' ),
			__( 'largest', 'all-in-one-seo-pack' ),
			__( 'high tech', 'all-in-one-seo-pack' ),
			__( 'refundable', 'all-in-one-seo-pack' ),
			__( 'monumental', 'all-in-one-seo-pack' ),
			__( 'colossal', 'all-in-one-seo-pack' ),
			__( 'latest', 'all-in-one-seo-pack' ),
			__( 'quickly', 'all-in-one-seo-pack' ),
			__( 'startling', 'all-in-one-seo-pack' ),
			__( 'now', 'all-in-one-seo-pack' ),
			__( 'important', 'all-in-one-seo-pack' ),
			__( 'revolutionary', 'all-in-one-seo-pack' ),
			__( 'quick', 'all-in-one-seo-pack' ),
			__( 'unlock', 'all-in-one-seo-pack' ),
			__( 'urgent', 'all-in-one-seo-pack' ),
			__( 'miracle', 'all-in-one-seo-pack' ),
			__( 'easy', 'all-in-one-seo-pack' ),
			__( 'fortune', 'all-in-one-seo-pack' ),
			__( 'amazing', 'all-in-one-seo-pack' ),
			__( 'magic', 'all-in-one-seo-pack' ),
			__( 'direct', 'all-in-one-seo-pack' ),
			__( 'authentic', 'all-in-one-seo-pack' ),
			__( 'exciting', 'all-in-one-seo-pack' ),
			__( 'proven', 'all-in-one-seo-pack' ),
			__( 'simple', 'all-in-one-seo-pack' ),
			__( 'announcing', 'all-in-one-seo-pack' ),
			__( 'portfolio', 'all-in-one-seo-pack' ),
			__( 'reward', 'all-in-one-seo-pack' ),
			__( 'strange', 'all-in-one-seo-pack' ),
			__( 'huge gift', 'all-in-one-seo-pack' ),
			__( 'revealing', 'all-in-one-seo-pack' ),
			__( 'weird', 'all-in-one-seo-pack' ),
			__( 'value', 'all-in-one-seo-pack' ),
			__( 'introducing', 'all-in-one-seo-pack' ),
			__( 'sensational', 'all-in-one-seo-pack' ),
			__( 'surprise', 'all-in-one-seo-pack' ),
			__( 'insider', 'all-in-one-seo-pack' ),
			__( 'practical', 'all-in-one-seo-pack' ),
			__( 'excellent', 'all-in-one-seo-pack' ),
			__( 'delighted', 'all-in-one-seo-pack' ),
			__( 'download', 'all-in-one-seo-pack' ),
		];
	}

	/**
	 * Returns a list of common words.
	 *
	 * @since 4.1.2
	 *
	 * @return array The list of common words.
	 */
	private function commonWords() {
		return [
			__( 'a', 'all-in-one-seo-pack' ),
			__( 'for', 'all-in-one-seo-pack' ),
			__( 'about', 'all-in-one-seo-pack' ),
			__( 'from', 'all-in-one-seo-pack' ),
			__( 'after', 'all-in-one-seo-pack' ),
			__( 'get', 'all-in-one-seo-pack' ),
			__( 'all', 'all-in-one-seo-pack' ),
			__( 'has', 'all-in-one-seo-pack' ),
			__( 'an', 'all-in-one-seo-pack' ),
			__( 'have', 'all-in-one-seo-pack' ),
			__( 'and', 'all-in-one-seo-pack' ),
			__( 'he', 'all-in-one-seo-pack' ),
			__( 'are', 'all-in-one-seo-pack' ),
			__( 'her', 'all-in-one-seo-pack' ),
			__( 'as', 'all-in-one-seo-pack' ),
			__( 'his', 'all-in-one-seo-pack' ),
			__( 'at', 'all-in-one-seo-pack' ),
			__( 'how', 'all-in-one-seo-pack' ),
			__( 'be', 'all-in-one-seo-pack' ),
			__( 'I', 'all-in-one-seo-pack' ),
			__( 'but', 'all-in-one-seo-pack' ),
			__( 'if', 'all-in-one-seo-pack' ),
			__( 'by', 'all-in-one-seo-pack' ),
			__( 'in', 'all-in-one-seo-pack' ),
			__( 'can', 'all-in-one-seo-pack' ),
			__( 'is', 'all-in-one-seo-pack' ),
			__( 'did', 'all-in-one-seo-pack' ),
			__( 'it', 'all-in-one-seo-pack' ),
			__( 'do', 'all-in-one-seo-pack' ),
			__( 'just', 'all-in-one-seo-pack' ),
			__( 'ever', 'all-in-one-seo-pack' ),
			__( 'like', 'all-in-one-seo-pack' ),
			__( 'll', 'all-in-one-seo-pack' ),
			__( 'these', 'all-in-one-seo-pack' ),
			__( 'me', 'all-in-one-seo-pack' ),
			__( 'they', 'all-in-one-seo-pack' ),
			__( 'most', 'all-in-one-seo-pack' ),
			__( 'things', 'all-in-one-seo-pack' ),
			__( 'my', 'all-in-one-seo-pack' ),
			__( 'this', 'all-in-one-seo-pack' ),
			__( 'no', 'all-in-one-seo-pack' ),
			__( 'to', 'all-in-one-seo-pack' ),
			__( 'not', 'all-in-one-seo-pack' ),
			__( 'up', 'all-in-one-seo-pack' ),
			__( 'of', 'all-in-one-seo-pack' ),
			__( 'was', 'all-in-one-seo-pack' ),
			__( 'on', 'all-in-one-seo-pack' ),
			__( 'what', 'all-in-one-seo-pack' ),
			__( 're', 'all-in-one-seo-pack' ),
			__( 'when', 'all-in-one-seo-pack' ),
			__( 'she', 'all-in-one-seo-pack' ),
			__( 'who', 'all-in-one-seo-pack' ),
			__( 'sould', 'all-in-one-seo-pack' ),
			__( 'why', 'all-in-one-seo-pack' ),
			__( 'so', 'all-in-one-seo-pack' ),
			__( 'will', 'all-in-one-seo-pack' ),
			__( 'that', 'all-in-one-seo-pack' ),
			__( 'with', 'all-in-one-seo-pack' ),
			__( 'the', 'all-in-one-seo-pack' ),
			__( 'you', 'all-in-one-seo-pack' ),
			__( 'their', 'all-in-one-seo-pack' ),
			__( 'your', 'all-in-one-seo-pack' ),
			__( 'there', 'all-in-one-seo-pack' ),
		];
	}

	/**
	 * Returns a list of uncommon words.
	 *
	 * @since 4.1.2
	 *
	 * @return array The list of uncommon words.
	 */
	private function uncommonWords() {
		return [
			__( 'actually', 'all-in-one-seo-pack' ),
			__( 'happened', 'all-in-one-seo-pack' ),
			__( 'need', 'all-in-one-seo-pack' ),
			__( 'thing', 'all-in-one-seo-pack' ),
			__( 'awesome', 'all-in-one-seo-pack' ),
			__( 'heart', 'all-in-one-seo-pack' ),
			__( 'never', 'all-in-one-seo-pack' ),
			__( 'think', 'all-in-one-seo-pack' ),
			__( 'baby', 'all-in-one-seo-pack' ),
			__( 'here', 'all-in-one-seo-pack' ),
			__( 'new', 'all-in-one-seo-pack' ),
			__( 'time', 'all-in-one-seo-pack' ),
			__( 'beautiful', 'all-in-one-seo-pack' ),
			__( 'its', 'all-in-one-seo-pack' ),
			__( 'now', 'all-in-one-seo-pack' ),
			__( 'valentines', 'all-in-one-seo-pack' ),
			__( 'being', 'all-in-one-seo-pack' ),
			__( 'know', 'all-in-one-seo-pack' ),
			__( 'old', 'all-in-one-seo-pack' ),
			__( 'video', 'all-in-one-seo-pack' ),
			__( 'best', 'all-in-one-seo-pack' ),
			__( 'life', 'all-in-one-seo-pack' ),
			__( 'one', 'all-in-one-seo-pack' ),
			__( 'want', 'all-in-one-seo-pack' ),
			__( 'better', 'all-in-one-seo-pack' ),
			__( 'little', 'all-in-one-seo-pack' ),
			__( 'out', 'all-in-one-seo-pack' ),
			__( 'watch', 'all-in-one-seo-pack' ),
			__( 'boy', 'all-in-one-seo-pack' ),
			__( 'look', 'all-in-one-seo-pack' ),
			__( 'people', 'all-in-one-seo-pack' ),
			__( 'way', 'all-in-one-seo-pack' ),
			__( 'dog', 'all-in-one-seo-pack' ),
			__( 'love', 'all-in-one-seo-pack' ),
			__( 'photos', 'all-in-one-seo-pack' ),
			__( 'ways', 'all-in-one-seo-pack' ),
			__( 'down', 'all-in-one-seo-pack' ),
			__( 'made', 'all-in-one-seo-pack' ),
			__( 'really', 'all-in-one-seo-pack' ),
			__( 'world', 'all-in-one-seo-pack' ),
			__( 'facebook', 'all-in-one-seo-pack' ),
			__( 'make', 'all-in-one-seo-pack' ),
			__( 'reasons', 'all-in-one-seo-pack' ),
			__( 'year', 'all-in-one-seo-pack' ),
			__( 'first', 'all-in-one-seo-pack' ),
			__( 'makes', 'all-in-one-seo-pack' ),
			__( 'right', 'all-in-one-seo-pack' ),
			__( 'years', 'all-in-one-seo-pack' ),
			__( 'found', 'all-in-one-seo-pack' ),
			__( 'man', 'all-in-one-seo-pack' ),
			__( 'see', 'all-in-one-seo-pack' ),
			__( 'you’ll', 'all-in-one-seo-pack' ),
			__( 'girl', 'all-in-one-seo-pack' ),
			__( 'media', 'all-in-one-seo-pack' ),
			__( 'seen', 'all-in-one-seo-pack' ),
			__( 'good', 'all-in-one-seo-pack' ),
			__( 'mind', 'all-in-one-seo-pack' ),
			__( 'social', 'all-in-one-seo-pack' ),
			__( 'guy', 'all-in-one-seo-pack' ),
			__( 'more', 'all-in-one-seo-pack' ),
			__( 'something', 'all-in-one-seo-pack' ),
		];
	}

	/**
	 * Returns a list of emotional power words.
	 *
	 * @since 4.1.2
	 *
	 * @return array The list of emotional power words.
	 */
	private function emotionPowerWords() {
		return [
			__( 'destroy', 'all-in-one-seo-pack' ),
			__( 'extra', 'all-in-one-seo-pack' ),
			__( 'in a', 'all-in-one-seo-pack' ),
			__( 'devastating', 'all-in-one-seo-pack' ),
			__( 'eye-opening', 'all-in-one-seo-pack' ),
			__( 'gift', 'all-in-one-seo-pack' ),
			__( 'in the world', 'all-in-one-seo-pack' ),
			__( 'devoted', 'all-in-one-seo-pack' ),
			__( 'fail', 'all-in-one-seo-pack' ),
			__( 'in the', 'all-in-one-seo-pack' ),
			__( 'faith', 'all-in-one-seo-pack' ),
			__( 'grateful', 'all-in-one-seo-pack' ),
			__( 'inexpensive', 'all-in-one-seo-pack' ),
			__( 'dirty', 'all-in-one-seo-pack' ),
			__( 'famous', 'all-in-one-seo-pack' ),
			__( 'disastrous', 'all-in-one-seo-pack' ),
			__( 'fantastic', 'all-in-one-seo-pack' ),
			__( 'greed', 'all-in-one-seo-pack' ),
			__( 'grit', 'all-in-one-seo-pack' ),
			__( 'insanely', 'all-in-one-seo-pack' ),
			__( 'disgusting', 'all-in-one-seo-pack' ),
			__( 'fearless', 'all-in-one-seo-pack' ),
			__( 'disinformation', 'all-in-one-seo-pack' ),
			__( 'feast', 'all-in-one-seo-pack' ),
			__( 'insidious', 'all-in-one-seo-pack' ),
			__( 'dollar', 'all-in-one-seo-pack' ),
			__( 'feeble', 'all-in-one-seo-pack' ),
			__( 'gullible', 'all-in-one-seo-pack' ),
			__( 'double', 'all-in-one-seo-pack' ),
			__( 'fire', 'all-in-one-seo-pack' ),
			__( 'hack', 'all-in-one-seo-pack' ),
			__( 'fleece', 'all-in-one-seo-pack' ),
			__( 'had enough', 'all-in-one-seo-pack' ),
			__( 'invasion', 'all-in-one-seo-pack' ),
			__( 'drowning', 'all-in-one-seo-pack' ),
			__( 'floundering', 'all-in-one-seo-pack' ),
			__( 'happy', 'all-in-one-seo-pack' ),
			__( 'ironclad', 'all-in-one-seo-pack' ),
			__( 'dumb', 'all-in-one-seo-pack' ),
			__( 'flush', 'all-in-one-seo-pack' ),
			__( 'hate', 'all-in-one-seo-pack' ),
			__( 'irresistibly', 'all-in-one-seo-pack' ),
			__( 'hazardous', 'all-in-one-seo-pack' ),
			__( 'is the', 'all-in-one-seo-pack' ),
			__( 'fool', 'all-in-one-seo-pack' ),
			__( 'is what happens when', 'all-in-one-seo-pack' ),
			__( 'fooled', 'all-in-one-seo-pack' ),
			__( 'helpless', 'all-in-one-seo-pack' ),
			__( 'it looks like a', 'all-in-one-seo-pack' ),
			__( 'embarrass', 'all-in-one-seo-pack' ),
			__( 'for the first time', 'all-in-one-seo-pack' ),
			__( 'help are the', 'all-in-one-seo-pack' ),
			__( 'jackpot', 'all-in-one-seo-pack' ),
			__( 'forbidden', 'all-in-one-seo-pack' ),
			__( 'hidden', 'all-in-one-seo-pack' ),
			__( 'jail', 'all-in-one-seo-pack' ),
			__( 'empower', 'all-in-one-seo-pack' ),
			__( 'force-fed', 'all-in-one-seo-pack' ),
			__( 'high', 'all-in-one-seo-pack' ),
			__( 'jaw-dropping', 'all-in-one-seo-pack' ),
			__( 'forgotten', 'all-in-one-seo-pack' ),
			__( 'jeopardy', 'all-in-one-seo-pack' ),
			__( 'energize', 'all-in-one-seo-pack' ),
			__( 'hoax', 'all-in-one-seo-pack' ),
			__( 'jubilant', 'all-in-one-seo-pack' ),
			__( 'foul', 'all-in-one-seo-pack' ),
			__( 'hope', 'all-in-one-seo-pack' ),
			__( 'killer', 'all-in-one-seo-pack' ),
			__( 'frantic', 'all-in-one-seo-pack' ),
			__( 'horrific', 'all-in-one-seo-pack' ),
			__( 'know it all', 'all-in-one-seo-pack' ),
			__( 'epic', 'all-in-one-seo-pack' ),
			__( 'how to make', 'all-in-one-seo-pack' ),
			__( 'evil', 'all-in-one-seo-pack' ),
			__( 'freebie', 'all-in-one-seo-pack' ),
			__( 'frenzy', 'all-in-one-seo-pack' ),
			__( 'hurricane', 'all-in-one-seo-pack' ),
			__( 'excited', 'all-in-one-seo-pack' ),
			__( 'fresh on the mind', 'all-in-one-seo-pack' ),
			__( 'frightening', 'all-in-one-seo-pack' ),
			__( 'hypnotic', 'all-in-one-seo-pack' ),
			__( 'lawsuit', 'all-in-one-seo-pack' ),
			__( 'frugal', 'all-in-one-seo-pack' ),
			__( 'illegal', 'all-in-one-seo-pack' ),
			__( 'fulfill', 'all-in-one-seo-pack' ),
			__( 'lick', 'all-in-one-seo-pack' ),
			__( 'explode', 'all-in-one-seo-pack' ),
			__( 'lies', 'all-in-one-seo-pack' ),
			__( 'exposed', 'all-in-one-seo-pack' ),
			__( 'gambling', 'all-in-one-seo-pack' ),
			__( 'like a normal', 'all-in-one-seo-pack' ),
			__( 'nightmare', 'all-in-one-seo-pack' ),
			__( 'results', 'all-in-one-seo-pack' ),
			__( 'line', 'all-in-one-seo-pack' ),
			__( 'no good', 'all-in-one-seo-pack' ),
			__( 'pound', 'all-in-one-seo-pack' ),
			__( 'loathsome', 'all-in-one-seo-pack' ),
			__( 'no questions asked', 'all-in-one-seo-pack' ),
			__( 'revenge', 'all-in-one-seo-pack' ),
			__( 'lonely', 'all-in-one-seo-pack' ),
			__( 'looks like a', 'all-in-one-seo-pack' ),
			__( 'obnoxious', 'all-in-one-seo-pack' ),
			__( 'preposterous', 'all-in-one-seo-pack' ),
			__( 'revolting', 'all-in-one-seo-pack' ),
			__( 'looming', 'all-in-one-seo-pack' ),
			__( 'priced', 'all-in-one-seo-pack' ),
			__( 'lost', 'all-in-one-seo-pack' ),
			__( 'prison', 'all-in-one-seo-pack' ),
			__( 'lowest', 'all-in-one-seo-pack' ),
			__( 'of the', 'all-in-one-seo-pack' ),
			__( 'privacy', 'all-in-one-seo-pack' ),
			__( 'rich', 'all-in-one-seo-pack' ),
			__( 'lunatic', 'all-in-one-seo-pack' ),
			__( 'off-limits', 'all-in-one-seo-pack' ),
			__( 'private', 'all-in-one-seo-pack' ),
			__( 'risky', 'all-in-one-seo-pack' ),
			__( 'lurking', 'all-in-one-seo-pack' ),
			__( 'offer', 'all-in-one-seo-pack' ),
			__( 'prize', 'all-in-one-seo-pack' ),
			__( 'ruthless', 'all-in-one-seo-pack' ),
			__( 'lust', 'all-in-one-seo-pack' ),
			__( 'official', 'all-in-one-seo-pack' ),
			__( 'luxurious', 'all-in-one-seo-pack' ),
			__( 'on the', 'all-in-one-seo-pack' ),
			__( 'profit', 'all-in-one-seo-pack' ),
			__( 'scary', 'all-in-one-seo-pack' ),
			__( 'lying', 'all-in-one-seo-pack' ),
			__( 'outlawed', 'all-in-one-seo-pack' ),
			__( 'protected', 'all-in-one-seo-pack' ),
			__( 'scream', 'all-in-one-seo-pack' ),
			__( 'searing', 'all-in-one-seo-pack' ),
			__( 'overcome', 'all-in-one-seo-pack' ),
			__( 'provocative', 'all-in-one-seo-pack' ),
			__( 'make you', 'all-in-one-seo-pack' ),
			__( 'painful', 'all-in-one-seo-pack' ),
			__( 'pummel', 'all-in-one-seo-pack' ),
			__( 'secure', 'all-in-one-seo-pack' ),
			__( 'pale', 'all-in-one-seo-pack' ),
			__( 'punish', 'all-in-one-seo-pack' ),
			__( 'marked down', 'all-in-one-seo-pack' ),
			__( 'panic', 'all-in-one-seo-pack' ),
			__( 'quadruple', 'all-in-one-seo-pack' ),
			__( 'seductively', 'all-in-one-seo-pack' ),
			__( 'massive', 'all-in-one-seo-pack' ),
			__( 'pay zero', 'all-in-one-seo-pack' ),
			__( 'seize', 'all-in-one-seo-pack' ),
			__( 'meltdown', 'all-in-one-seo-pack' ),
			__( 'payback', 'all-in-one-seo-pack' ),
			__( 'might look like a', 'all-in-one-seo-pack' ),
			__( 'peril', 'all-in-one-seo-pack' ),
			__( 'mind-blowing', 'all-in-one-seo-pack' ),
			__( 'shameless', 'all-in-one-seo-pack' ),
			__( 'minute', 'all-in-one-seo-pack' ),
			__( 'rave', 'all-in-one-seo-pack' ),
			__( 'shatter', 'all-in-one-seo-pack' ),
			__( 'piranha', 'all-in-one-seo-pack' ),
			__( 'reckoning', 'all-in-one-seo-pack' ),
			__( 'shellacking', 'all-in-one-seo-pack' ),
			__( 'mired', 'all-in-one-seo-pack' ),
			__( 'pitfall', 'all-in-one-seo-pack' ),
			__( 'reclaim', 'all-in-one-seo-pack' ),
			__( 'mistakes', 'all-in-one-seo-pack' ),
			__( 'plague', 'all-in-one-seo-pack' ),
			__( 'sick and tired', 'all-in-one-seo-pack' ),
			__( 'money', 'all-in-one-seo-pack' ),
			__( 'played', 'all-in-one-seo-pack' ),
			__( 'refugee', 'all-in-one-seo-pack' ),
			__( 'silly', 'all-in-one-seo-pack' ),
			__( 'money-grubbing', 'all-in-one-seo-pack' ),
			__( 'pluck', 'all-in-one-seo-pack' ),
			__( 'refund', 'all-in-one-seo-pack' ),
			__( 'moneyback', 'all-in-one-seo-pack' ),
			__( 'plummet', 'all-in-one-seo-pack' ),
			__( 'plunge', 'all-in-one-seo-pack' ),
			__( 'murder', 'all-in-one-seo-pack' ),
			__( 'pointless', 'all-in-one-seo-pack' ),
			__( 'sinful', 'all-in-one-seo-pack' ),
			__( 'myths', 'all-in-one-seo-pack' ),
			__( 'poor', 'all-in-one-seo-pack' ),
			__( 'remarkably', 'all-in-one-seo-pack' ),
			__( 'six-figure', 'all-in-one-seo-pack' ),
			__( 'never again', 'all-in-one-seo-pack' ),
			__( 'research', 'all-in-one-seo-pack' ),
			__( 'surrender', 'all-in-one-seo-pack' ),
			__( 'to the', 'all-in-one-seo-pack' ),
			__( 'varify', 'all-in-one-seo-pack' ),
			__( 'skyrocket', 'all-in-one-seo-pack' ),
			__( 'toxic', 'all-in-one-seo-pack' ),
			__( 'vibrant', 'all-in-one-seo-pack' ),
			__( 'slaughter', 'all-in-one-seo-pack' ),
			__( 'swindle', 'all-in-one-seo-pack' ),
			__( 'trap', 'all-in-one-seo-pack' ),
			__( 'victim', 'all-in-one-seo-pack' ),
			__( 'sleazy', 'all-in-one-seo-pack' ),
			__( 'taboo', 'all-in-one-seo-pack' ),
			__( 'treasure', 'all-in-one-seo-pack' ),
			__( 'victory', 'all-in-one-seo-pack' ),
			__( 'smash', 'all-in-one-seo-pack' ),
			__( 'tailspin', 'all-in-one-seo-pack' ),
			__( 'vindication', 'all-in-one-seo-pack' ),
			__( 'smug', 'all-in-one-seo-pack' ),
			__( 'tank', 'all-in-one-seo-pack' ),
			__( 'triple', 'all-in-one-seo-pack' ),
			__( 'viral', 'all-in-one-seo-pack' ),
			__( 'smuggled', 'all-in-one-seo-pack' ),
			__( 'tantalizing', 'all-in-one-seo-pack' ),
			__( 'triumph', 'all-in-one-seo-pack' ),
			__( 'volatile', 'all-in-one-seo-pack' ),
			__( 'sniveling', 'all-in-one-seo-pack' ),
			__( 'targeted', 'all-in-one-seo-pack' ),
			__( 'truth', 'all-in-one-seo-pack' ),
			__( 'vulnerable', 'all-in-one-seo-pack' ),
			__( 'snob', 'all-in-one-seo-pack' ),
			__( 'tawdry', 'all-in-one-seo-pack' ),
			__( 'try before you buy', 'all-in-one-seo-pack' ),
			__( 'tech', 'all-in-one-seo-pack' ),
			__( 'turn the tables', 'all-in-one-seo-pack' ),
			__( 'wanton', 'all-in-one-seo-pack' ),
			__( 'soaring', 'all-in-one-seo-pack' ),
			__( 'warning', 'all-in-one-seo-pack' ),
			__( 'teetering', 'all-in-one-seo-pack' ),
			__( 'unauthorized', 'all-in-one-seo-pack' ),
			__( 'spectacular', 'all-in-one-seo-pack' ),
			__( 'temporary fix', 'all-in-one-seo-pack' ),
			__( 'unbelievably', 'all-in-one-seo-pack' ),
			__( 'spine', 'all-in-one-seo-pack' ),
			__( 'tempting', 'all-in-one-seo-pack' ),
			__( 'uncommonly', 'all-in-one-seo-pack' ),
			__( 'what happened', 'all-in-one-seo-pack' ),
			__( 'spirit', 'all-in-one-seo-pack' ),
			__( 'what happens when', 'all-in-one-seo-pack' ),
			__( 'terror', 'all-in-one-seo-pack' ),
			__( 'under', 'all-in-one-seo-pack' ),
			__( 'what happens', 'all-in-one-seo-pack' ),
			__( 'staggering', 'all-in-one-seo-pack' ),
			__( 'underhanded', 'all-in-one-seo-pack' ),
			__( 'what this', 'all-in-one-seo-pack' ),
			__( 'that will make you', 'all-in-one-seo-pack' ),
			__( 'undo","when you see', 'all-in-one-seo-pack' ),
			__( 'that will make', 'all-in-one-seo-pack' ),
			__( 'unexpected', 'all-in-one-seo-pack' ),
			__( 'when you', 'all-in-one-seo-pack' ),
			__( 'strangle', 'all-in-one-seo-pack' ),
			__( 'that will', 'all-in-one-seo-pack' ),
			__( 'whip', 'all-in-one-seo-pack' ),
			__( 'the best', 'all-in-one-seo-pack' ),
			__( 'whopping', 'all-in-one-seo-pack' ),
			__( 'stuck up', 'all-in-one-seo-pack' ),
			__( 'the ranking of', 'all-in-one-seo-pack' ),
			__( 'wicked', 'all-in-one-seo-pack' ),
			__( 'stunning', 'all-in-one-seo-pack' ),
			__( 'the most', 'all-in-one-seo-pack' ),
			__( 'will make you', 'all-in-one-seo-pack' ),
			__( 'stupid', 'all-in-one-seo-pack' ),
			__( 'the reason why is', 'all-in-one-seo-pack' ),
			__( 'unscrupulous', 'all-in-one-seo-pack' ),
			__( 'thing ive ever seen', 'all-in-one-seo-pack' ),
			__( 'withheld', 'all-in-one-seo-pack' ),
			__( 'this is the', 'all-in-one-seo-pack' ),
			__( 'this is what happens', 'all-in-one-seo-pack' ),
			__( 'unusually', 'all-in-one-seo-pack' ),
			__( 'wondrous', 'all-in-one-seo-pack' ),
			__( 'this is what', 'all-in-one-seo-pack' ),
			__( 'uplifting', 'all-in-one-seo-pack' ),
			__( 'worry', 'all-in-one-seo-pack' ),
			__( 'sure', 'all-in-one-seo-pack' ),
			__( 'this is', 'all-in-one-seo-pack' ),
			__( 'wounded', 'all-in-one-seo-pack' ),
			__( 'surge', 'all-in-one-seo-pack' ),
			__( 'thrilled', 'all-in-one-seo-pack' ),
			__( 'you need to know', 'all-in-one-seo-pack' ),
			__( 'thrilling', 'all-in-one-seo-pack' ),
			__( 'valor', 'all-in-one-seo-pack' ),
			__( 'you need to', 'all-in-one-seo-pack' ),
			__( 'you see what', 'all-in-one-seo-pack' ),
			__( 'surprising', 'all-in-one-seo-pack' ),
			__( 'tired', 'all-in-one-seo-pack' ),
			__( 'you see', 'all-in-one-seo-pack' ),
			__( 'surprisingly', 'all-in-one-seo-pack' ),
			__( 'to be', 'all-in-one-seo-pack' ),
			__( 'vaporize', 'all-in-one-seo-pack' ),
		];
	}
}