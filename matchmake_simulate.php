<?php

function simulate_easy_bot() {

    global $json, $GameMode, $count_winning, $count_losing;

    /*
     * Match pertama kali (tidak ada History) akan melawan easy bot 
     */
    if (!isset($json->HistoryWin) || !is_array($json->HistoryWin) || count($json->HistoryWin) < 1) {
        return true;
    }

    $is_count_winning = true;
    $is_count_losing = true;
    $count_game = 0;
    $is_count_game = true;
    
    $HistoryWin = $json->HistoryWin;

    foreach ($HistoryWin as $item) {
        if ($is_count_winning) {
            if ($item->Result == "Win") {
                $count_winning++;
            } else {
                $is_count_winning = FALSE;
            }
        }
        if ($is_count_losing) {
            if ($item->Result == "Lose") {
                $count_losing++;
            } else {
                $is_count_losing = FALSE;
            }
        }
        if ($is_count_game) {
            if ($item->Mode == $GameMode) {
                $count_game++;
            } else {
                $is_count_game = FALSE;
            }            
        }
    }

    /*
     * Match Pertama di GameMode yang berbeda dengan sebelumnya akan melawan easy bot
     * Untuk $10 akan langsung melawan easy bot
     * Untuk $100, $1K, $10K, $100K, $1M hanya akan melawan bot jika total kemenangan sebelumnya belum 2x
     */
    if (isset($HistoryWin[0]) && isset($HistoryWin[0]->Mode) && $HistoryWin[0]->Mode != $GameMode) {
        if ($GameMode == "$10" || $count_winning < 2) {
            return true;
        }
    }

    if (isset($HistoryWin[0]) && isset($HistoryWin[0]->Mode) && $HistoryWin[0]->Mode == $GameMode) {
        
        /*
         * Jika sebelumnya sudah kalah 2x akan bertemu easy bot
         */
        if ($count_losing >= 2) {
            return true;
        }
        
        /*
         * GameMode $10 akan bertemu easy bot di match : 1, 2, & 4
         */
        if ($GameMode == "$10" && in_array($count_game, array(1, 3))) {
            return true;
        }

        /*
         * GameMode $100 akan bertemu easy bot di match : 1 & 4
         */
        if ($GameMode == "$100" && in_array($count_game, array(3))) {
            return true;
        }

        /*
         * GameMode $1K akan bertemu easy bot di match : 1 
         */
        
        /*
         * GameMode $10K akan bertemu easy bot di match : 1 
         */
        
        /*
         * GameMode $100K akan bertemu easy bot di match : 1 
         */
        
        /*
         * GameMode $1M akan bertemu easy bot di match : 1 
         */
        
    }

    return false;
}
