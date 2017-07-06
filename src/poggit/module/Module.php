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

namespace poggit\module;

use poggit\account\Session;
use poggit\errdoc\AccessDeniedPage;
use poggit\errdoc\BadRequestPage;
use poggit\errdoc\NotFoundPage;
use poggit\errdoc\SimpleNotFoundPage;
use poggit\Mbd;
use poggit\Meta;
use poggit\utils\OutputManager;

abstract class Module {
    /** @var Module|null */
    public static $currentPage = null;

    /** @var string */
    private $query;

    public function __construct(string $query) {
        $this->query = $query;
    }

    public function getQuery() {
        return $this->query;
    }

    public abstract function getName(): string;

    public function getAllNames(): array {
        return [$this->getName()];
    }

    public abstract function output();

    public function errorNotFound(bool $simple = false) {
        OutputManager::terminateAll();
        if($simple) {
            (new SimpleNotFoundPage(""))->output();
        } else {
            (new NotFoundPage($this->getName() . "/" . $this->query))->output();
        }
        die;
    }

    public function errorAccessDenied(string $details = null) {
        OutputManager::terminateAll();
        $page = new AccessDeniedPage($this->getName() . "/" . $this->query);
        if($details !== null) $page->details = $details;
        $page->output();
        die;
    }

    public function errorBadRequest(string $message, bool $escape = true) {
        OutputManager::terminateAll();
        (new BadRequestPage($message, $escape))->output();
        die;
    }

    protected function headIncludes(string $title, $description = "", $type = "website", string $shortUrl = "", array $extraKeywords = []) {
        global $requestPath;
        ?>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description"
              content="<?= Mbd::esq($title) == "Poggit" ? "Poggit: The PocketMine Plugin Platform" : Mbd::esq($title) . " Plugin for PocketMine" ?>">
        <meta name="keywords"
              content="<?= implode(",", array_merge([Mbd::esq($title)], $extraKeywords)) ?>,plugin,PocketMine,pocketmine plugins,MCPE plugins,Poggit,PocketMine-MP,PMMP"/>
        <meta property="og:site_name" content="Poggit"/>
        <meta property="og:image" content="<?= Meta::getSecret("meta.extPath") ?>res/poggit.png"/>
        <meta property="og:title" content="<?= Mbd::esq($title) ?>"/>
        <meta property="og:type" content="<?= $type ?>"/>
        <meta property="og:url" content="<?= strlen($shortUrl) > 0 ? Mbd::esq($shortUrl) :
            (Meta::getSecret("meta.extPath") . Mbd::esq($requestPath === "/" ? "" : $requestPath ?? "")) ?>"/>
        <meta name="twitter:card" content="summary"/>
        <meta name="twitter:site" content="poggitci"/>
        <meta name="twitter:title" content="<?= Mbd::esq($title) ?>"/>
        <meta name="twitter:description" content="<?= Mbd::esq($description) ?>"/>
        <meta name="theme-color" content="#292b2c">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="mobile-web-app-capable" content="yes">
        <link type="image/x-icon" rel="icon" href="<?= Meta::root() ?>res/poggit.ico">
        <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
        <?php if(Meta::isDebug()) { ?>
<!--            <script src="https://code.jquery.com/jquery-migrate-3.0.0.js"></script>-->
        <?php } ?>
        <?php
        $this->includeCss("jquery-ui.min");
        $this->includeCss("bootstrap.min");
        $this->includeCss("style");
        $this->includeCss("toggles");
        $this->includeCss("toggles-light");
        $this->includeCss("jquery.paginate");

        $this->includeJs("bootstrap.min");
        $this->includeJs("jquery-ui.min");
        $this->includeJs("jquery.form");
        $this->includeJs("mobile");
        $this->includeJs("jQuery-UI-Dialog-extended");
        // put session.js before std
//        $this->includeJs("session");
        ResModule::echoSessionJs(true); // better performance
        $this->includeJs("std"); // TODO move to body footer
        $this->includeJs("toggles.min");
        $this->includeJs("jquery.paginate");
        if(!Session::getInstance()->tosHidden()) $this->includeJs("remindTos");
    }

    protected function bodyHeader() {
        $session = Session::getInstance();
        ?>
        <script>
            document.write('<style type="text/css">body{visibility:hidden}</style>');
            jQuery(function($) {
                $('body').css('visibility', 'visible');
            });
            (function(i, s, o, g, r, a, m) {
                i['GoogleAnalyticsObject'] = r;
                i[r] = i[r] || function() {
                    (i[r].q = i[r].q || []).push(arguments)
                }, i[r].l = 1 * new Date();
                a = s.createElement(o),
                    m = s.getElementsByTagName(o)[0];
                a.async = 1;
                a.src = g;
                m.parentNode.insertBefore(a, m)
            })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

            ga('create', 'UA-93677016-1', 'auto');
            ga('send', 'pageview');
        </script>
        <div id="header" class="container-fluid">
            <nav class="navbar navbar-toggleable-md navbar-inverse bg-inverse fixed-top" role="navigation">
                <div class="tabletlogo">
                    <div class="navbar-brand tm">
                        <a href="<?= Meta::root() ?>">
                            <img class="logo" src="<?= Meta::root() ?>res/poggit.png"/>
                            Poggit
                            <?php if(Meta::$GIT_REF !== "" and Meta::$GIT_REF !== "master" and Meta::$GIT_REF !== "deploy") { ?>
                                <sub style="padding-left: 5px;"><?= Meta::$GIT_REF === "tmp" ? "test" : Meta::$GIT_REF ?></sub>
                            <?php } ?>
                        </a></div>
                    <button class="navbar-toggler navbar-toggler-right mr-auto" type="button" data-toggle="collapse"
                            data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false"
                            aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                </div>
                <ul class="navbar-nav navbuttons collapse navbar-collapse">
                    <li class="nav-item navbutton" data-target="">Home</li>
                    <li class="nav-item navbutton" data-target="ci/recent">CI</li>
                    <li class="nav-item navbutton" data-target="ci/pmmp/PocketMine-MP/~?branch=master">PMMP</li>
                    <li class="nav-item navbutton" data-target="plugins">Release</li>
                    <li class="nav-item navbutton" data-target="review">Review</li>
                    <li class="nav-item navbutton" data-target="help">Help</li>
                </ul>
                <div id="navbarNavAltMarkup" class="navbuttons collapse navbar-collapse">
                    <ul class="navbar-nav navbuttons collapse navbar-collapse">
                        <?php if($session->isLoggedIn()) { ?>
                            <li class="nav-item loginbuttons"><span
                                        onclick="logout()">Logout as <?= htmlspecialchars($session->getName()) ?></span>
                            </li>
                            <li class="nav-item loginbuttons"><span
                                        onclick="login(undefined, true)">Change Scopes</span>
                            </li>
                        <?php } else { ?>
                            <li class="nav-item loginbuttons"><span onclick='login()'>Login with GitHub</span></li>
                            <li class="nav-item loginbuttons"><span onclick="login(undefined, true)">Custom Login</span>
                            </li>
                        <?php } ?>
                        <?php if(Meta::getUserAccess($session->getName()) === Meta::ADM) { ?>
                            <li class="loginbuttons"><span
                                        onclick='ajax("login.su", {data: {target: prompt("su")}, success: function() { window.location.reload(true); }})'><code>su</code></span>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </nav>
        </div>
        <?php
    }

    protected function bodyFooter() {
        ?>
        <script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>
        <div id="footer">
            <ul class="footernavbar">
                <li>Powered by Poggit <?= !Meta::isDebug() ? Meta::POGGIT_VERSION :
                        ("<a href='https://github.com/poggit/poggit/tree/" . Meta::$GIT_REF . "'>" . Meta::$GIT_REF . "</a>") ?>
                    <?php if(Meta::isDebug()) { ?>
                        (@<a href="https://github.com/poggit/poggit/tree/<?= Meta::$GIT_COMMIT ?>"><?=
                            substr(Meta::$GIT_COMMIT, 0, 7) ?></a>)
                    <?php } ?>
                </li>
                <li>&copy; <?= date("Y") ?> Poggit</li>
                <li><?= Meta::$onlineUsers ?? 0 ?> online</li>
            </ul>
            <ul class="footernavbar">
                <li><a href="<?= Meta::root() ?>tos">Terms of Service</a></li>
                <li><a target="_blank" href="https://gitter.im/poggit/Lobby">Contact Us</a></li>
                <li><a target="_blank" href="https://github.com/poggit/poggit">Source Code</a></li>
                <li><a target="_blank" href="https://github.com/poggit/poggit/issues">Report Bugs</a></li>
                <li><a href="https://twitter.com/poggitci" class="twitter-follow-button" data-show-screen-name="false"
                       data-show-count="true">Follow @poggitci</a></li>
                <li><a href="#" onclick="$('html, body').animate({scrollTop: 0},500);">Back to Top</a></li>
            </ul>
        </div>
        <?php
    }

    public function includeJs(string $fileName, bool $async = false) {
//        if(isset($_REQUEST["xIncludeAssetsDirect"])) {
//            echo "<script>";
//            readfile(JS_DIR . $fileName . ".js");
//            echo "</script>";
//            return;
//        }
        ?>
        <script type="text/javascript" src="<?= Meta::root() ?>js/<?= Mbd::esq($fileName) ?>.js" <?=
        $async ? "async" : "" ?>></script>
        <?php
    }

    public function includeCss(string $fileName) {
//        if(isset($_REQUEST["xIncludeAssetsDirect"])) {
//            echo "<style>";
//            readfile(RES_DIR . $fileName . ".css");
//            echo "</style>";
//            return;
//        }
        ?>
        <link type="text/css" rel="stylesheet" href="<?= Meta::root() ?>res/<?= Mbd::esq($fileName) ?>.css">
        <?php
    }

    protected function param(string $name, array $array = null) {
        if($array === null) $array = $_REQUEST;
        if(!isset($array[$name])) $this->errorBadRequest("Missing parameter '$name'");
        return $array[$name];
    }
}
