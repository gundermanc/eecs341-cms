<?php
require_once('../third_party/diff_match_patch_php/diff_match_patch.php');

/**
 * Performs delta encoding by diffing the new text against the old
 * old text and storing the deltas. These deltas can be applied to
 * the old text individually using applyDelta, or collectively using
 * assembleDeltas.
 * 
 * Diff and patch functionality provided by the wonderful diff_match_patch
 * library: https://github.com/nuxodin/diff_match_patch-php.
 */
class DeltaEncoder {

  /**
   * Performs a diff between oldText and newText and returns a diff output
   * serialized into a string in GNU style.
   */
  public static function encodeDelta($oldText, $newText) {
    $dmp = self::dmp();

    return $dmp->patch_toText($dmp->patch_make($oldText, $newText));
  }

  /**
   * Applies a delta to oldText. NOTE: Performs no error checking. If oldText
   * does not match the text used to produce the diff, the patch will not be
   * applied.
   */
  public static function applyDelta($oldText, $delta) {
    $dmp = self::dmp();

    $patchOpResult = $dmp->patch_apply($dmp->patch_fromText($delta), $oldText);

    // Return the string component containing the new text.
    return $patchOpResult[0];
  }

  /**
   * Assembles an array of deltas into a string. 
   * NOTE: Performs no error checking. Each entry must have been diffed using
   * the result of the previous entry. The original oldText value for the first
   * array item must have been "" the empty string.
   * Returns: a string containing the output string.
   */
  public static function assembleDeltas($deltaArray) {
    $result = "";

    // Apply each of the deltas one-by-one.
    for ($i = 0; $i < count($deltaArray); $i++) {
      $result = self::applyDelta($result, $deltaArray[$i]);
    }

    return $result;
  }

  /**
   * Get standard instance of diff, match patch.
   */
  private static function dmp() {
    $dmp = new diff_match_patch();

    return $dmp;
  }

}

?>
