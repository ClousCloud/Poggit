<?php

/*
 * Poggit
 *
 * Copyright (C) 2016-2017 Poggit
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace poggit;

use stdClass;

class Mbd {
    public static function showBuildNumbers(int $global, int $internal, string $link = "") {
        if(strlen($link) > 0) { ?>
            <a href="<?= Meta::root() . Mbd::esq($link) ?>">
        <?php } ?>
        <span style='font-family:"Courier New", monospace;'>
            #<?= $internal ?> (&amp;<?= strtoupper(dechex($global)) ?>)
        </span>
        <?php if(strlen($link) > 0) { ?>
            </a>
        <?php } ?>
        <sup class="hover-title" title="#<?= $internal ?> is the internal build number for your project.
&amp;<?= strtoupper(dechex($global)) ?> is a unique build ID for all Poggit CI builds">(?)</sup>
        <?php
    }

    public static function ghLink(string $url, int $width = 16) {
        $markUrl = Meta::root() . "res/ghMark.png";
        $url = Mbd::esq($url);
        echo "<a href='$url' target='_blank'>";
        echo "<img class='gh-logo' src='$markUrl' width='$width'/>";
        echo "</a>";
    }

    /**
     * @param string|stdClass $owner
     * @param string|int      $avatar      default ""
     * @param int             $avatarWidth default 16
     * @param bool            $showGh      default false
     */
    public static function displayUser($owner, $avatar = "", int $avatarWidth = 16, bool $showGh = false) {
        if($owner instanceof stdClass) {
            Mbd::displayUser($owner->login, $owner->avatar_url, $avatar ?: 16);
            return;
        }
        if($avatar !== "") {
            $avatar = Mbd::esq($avatar);
            echo "<img src='$avatar' width='$avatarWidth'/> ";
        }
        $owner = htmlspecialchars($owner, ENT_QUOTES);
        echo $owner, " ";
        if($showGh) Mbd::ghLink("https://github.com/$owner");
    }

    public static function displayRepo(string $owner, string $repo, string $avatar = "", int $avatarWidth = 16) {
        Mbd::displayUser($owner, $avatar, $avatarWidth);
        echo " / ";
        $repo = htmlspecialchars($repo, ENT_QUOTES);
        echo $repo, " ";
        Mbd::ghLink("https://github.com/$owner/$repo");
    }

    public static function displayAnchor($name) {
        $name = htmlspecialchars($name, ENT_QUOTES);
        ?>
        <a class="dynamic-anchor" id="anchor-<?= $name ?>" name="<?= $name ?>" href="#<?= $name ?>">&sect;</a>
        <?php
    }

    public static function copyable(string $label, string $value) {
        ?>
        <div class="copied remark" style="display: none"><span>Copied to clipboard</span></div>
        <a href="#"
           onclick='onCopyableClick(this)'><?= $label ?>:</a>
        <input type="text" value="<?= htmlspecialchars($value, ENT_QUOTES | ENT_HTML5) ?>"
               size="<?= ceil(strlen($value) * 0.95) ?>"/>
        <?php
    }

    public static function esq(string $string): string {
        return htmlspecialchars($string, ENT_QUOTES);
    }
}
