<?php

/**
 * DokuWiki Plugin navpath (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Olaf Steinicke <olaf.steinicke@ascilium.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class syntax_plugin_navpath extends DokuWiki_Syntax_Plugin
{
    private $split = '';

    /**
     * @return string Plugin Info
     */
    public function getInfo()
    {
        return confToHash(dirname(__FILE__) . '/plugin.info.txt');
    }

    /**
     * @return string Syntax mode type
     */
    public function getType()
    {
        return 'formatting';
    }

    public function getAllowedTypes()
    {
        return array('formatting', 'substitution', 'disabled');
    }
    /**
     * @return string Paragraph type
     */
    public function getPType()
    {
        return 'normal';
    }

    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort()
    {
        return 999;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern('<navpath.*?>(?=.*?<\/navpath>)', $mode, 'plugin_navpath');
    }

    public function postConnect()
    {
        $this->Lexer->addExitPattern('</navpath>', 'plugin_navpath');
    }

    /**
     * Handle matches of the navpath syntax
     *
     * @param string       $match   The match of the syntax
     * @param int          $state   The state of the handler
     * @param int          $pos     The position in the document
     * @param Doku_Handler $handler The handler
     *
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        switch ($state) {
            case DOKU_LEXER_ENTER: {
                    $split = preg_split("/split=/", substr($match, 8, -1), 2)[1];
                    $this->split = strlen($split) > 0 ? $split : ',';
                    return array($state, '');
                }
            case DOKU_LEXER_UNMATCHED: {
                    $data = explode($this->split, $match);
                    return array($state, $data);
                }
            case DOKU_LEXER_EXIT: {
                    return array($state, '');
                }
        }
        return false;
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string        $mode     Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer $renderer The renderer
     * @param array         $data     The data from the handler() function
     *
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer $renderer, $indata)
    {
        if ($mode !== 'xhtml') {
            return false;
        }

        list($state, $data) = $indata;
        switch ($state) {
            case DOKU_LEXER_ENTER:
                $renderer->doc .= '<span class="navpath"><img src="lib/plugins/navpath/images/nav_start.gif">&nbsp;';
                break;

            case DOKU_LEXER_UNMATCHED:
                foreach ($data as $key => $value) {
                    if ($key !== 0) {
                        $renderer->doc .= '&nbsp;<img src="lib/plugins/navpath/images/nav_step.gif">&nbsp;';
                    }
                    $renderer->emphasis_open();
                    $renderer->strong_open();
                    $renderer->doc .= $renderer->_xmlEntities($value);
                    $renderer->strong_close();
                    $renderer->emphasis_close();
                }
                break;

            case DOKU_LEXER_EXIT:
                $renderer->doc .= '&nbsp;<img src="lib/plugins/navpath/images/nav_end.gif"></span>';
                break;
        }
        return true;
    }
}
