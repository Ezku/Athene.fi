/*
When you try out a URL, it is matched against all the regexes. Every non-matching rule gets hidden. The first, "winning" match gets a highlight.

This is done by adding or removing the classes 'rewrite-rule-matched', 'rewrite-rule-matched-first' and 'rewrite-rule-unmatched'.

The regexes are stored in the global variable 'Monkeyman_Rewrite_Analyzer_Regexes'.
*/

jQuery(document).ready(function($) {
    // Highlight corresponding regex groups and their targets in the "Substitution" column
    $('span.regexgroup, span.regexgroup-target').hover(
        function() {
            var id = $(this)[0].id;
            if (id.substr(-7) == '-target') {
                id = id.substr(0, id.length - 7);
            }
            $('#' + id + ', #' + id + '-target').toggleClass('highlight');
        }
    );
    
    // Highlight the target of a repeater
    $('span.regex-repeater').hover(
        function() {
            $(this).parent().toggleClass('highlight');
        }
    );
    
    // Slide out the extra help
    $('#regex-help-link').click(function () {
        $('#regex-help-wrap').slideToggle('fast');
        
        return false;
    });
    
    var idxFirstMatchedRewriteRule = null;
    
    $('#monkeyman-regex-tester').keyup(function() {
        var url = $(this).val();
        
        if ( '' == url ) {
            // Empty box, show all rules
            $('.rewrite-rule-line').removeClass('rewrite-rule-matched rewrite-rule-matched-first rewrite-rule-unmatched');
            return;
        }
        
        var matchedRules = {};
        var result;
        var isFirst = true;
        for ( var idx in Monkeyman_Rewrite_Analyzer_Regexes ) {
            if ( result = Monkeyman_Rewrite_Analyzer_Regexes[idx].exec( url ) ) {
                // If it is a match, show it
                matchedRules[idx] = result;
                var elRule = $('#rewrite-rule-' + idx).addClass('rewrite-rule-matched').removeClass('rewrite-rule-unmatched');
                
                // Fill in the corresponding query values
                for ( var rIdx = 0; rIdx < result.length; rIdx++ ) {
                    $('#regex-' + idx + '-group-' + rIdx + '-target-value').html(result[rIdx] || '');
                }
                
                if ( isFirst ) {
                    // If it is the first match, highlight it
                    elRule.addClass('rewrite-rule-matched-first');
                    isFirst = false;
                    if ( idxFirstMatchedRewriteRule != idx ) {
                        // The previous first match is not longer the first match
                        $('#rewrite-rule-' + idxFirstMatchedRewriteRule).removeClass('rewrite-rule-matched-first');
                        idxFirstMatchedRewriteRule = idx;
                    }
                }
            } else {
                // If it is not a match, hide it
                $('#rewrite-rule-' + idx).removeClass('rewrite-rule-matched').addClass('rewrite-rule-unmatched');
            }
        }
    });
    
    // Clear the tester and show all rules
    $('#monkeyman-regex-tester-clear').click(function () {
        $('#monkeyman-regex-tester').val('');
        $('.rewrite-rule-line').removeClass('rewrite-rule-matched rewrite-rule-matched-first rewrite-rule-unmatched');
    });
    
    // Compile all regexes
    for ( var idx in Monkeyman_Rewrite_Analyzer_Regexes ) {
        var pattern = Monkeyman_Rewrite_Analyzer_Regexes[idx];
        var regex = new RegExp( '^' + pattern );
        Monkeyman_Rewrite_Analyzer_Regexes[idx] = regex;
    }
});