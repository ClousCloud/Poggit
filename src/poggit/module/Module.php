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
use poggit\ci\ui\BuildModule;
use poggit\errdoc\AccessDeniedPage;
use poggit\errdoc\BadRequestPage;
use poggit\errdoc\NotFoundPage;
use poggit\errdoc\SimpleNotFoundPage;
use poggit\Mbd;
use poggit\Meta;
use poggit\release\index\ReleaseListModule;
use poggit\utils\lang\Lang;
use poggit\utils\OutputManager;
use const poggit\JS_DIR;
use const poggit\RES_DIR;

abstract class Module {
    /** @var Module|null */
    public static $currentPage = null;

    public static $jsList = [
        "bootstrap",
        "jquery-ui",
        "toggles",
        "jquery.form",
        "mobile",
        "std",
        "jquery.paginate",
    ];

    /** @var string */
    private $query;

    public function __construct(string $query) {
        $this->query = $query;
    }

    public function getQuery(): string {
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
        <?php ResModule::echoSessionJs(true); // prevent round-trip -> faster loading; send before GA ?>
        <?php
//        @formatter:off
        ?>
      <script> <!-- Google Analytics -->
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');ga('create','UA-93677016-1','auto');ga('send','pageview');ga('set','dimension1',sessionData.session.isLoggedIn?'true':'false');
      </script>
        <?php
//        @formatter:on
        ?>
        <?php
        self::includeCss("jquery-ui.min");
        self::includeCss("bootstrap.min");
        self::includeCss("style.min");
        self::includeCss("toggles.min");
        self::includeCss("toggles-light.min");
        self::includeCss("jquery.paginate.min");
    }

    protected function flushJsList(bool $min = true) {
        ?>
      <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
        <?php
        foreach(self::$jsList as $script) {
            self::includeJs($script . ($min ? ".min" : ""));
        }
    }

    protected function bodyHeader() {
        $session = Session::getInstance();
        ?>
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
                <div class="navbar-middle">
                    <ul class="navbar-nav navbuttons collapse navbar-collapse">
                        <li class="nav-item navbutton" data-target="">Home</li>
                        <li class="nav-item navbutton" data-target="plugins"><?= ReleaseListModule::DISPLAY_NAME ?></li>
                        <li class="nav-item navbutton" data-target="ci/recent"><?= BuildModule::DISPLAY_NAME ?></li>
                        <?php if($session->isLoggedIn()) { ?>
                            <li class="nav-item navbutton" data-target="review">Review</li>
                        <?php } ?>
<!--                        <li class="nav-item navbutton extlink" data-target="https://poggit.github.io/support">Help</li>-->
                      <!-- TODO Finish the Help page, then add this back -->
                    </ul>
                </div>
                <div id="navbarNavAltMarkup" class="navbar-right navbuttons collapse navbar-collapse">
                    <ul class="navbar-nav">
                        <?php if($session->isLoggedIn()) { ?>
                            <li class="nav-item loginbuttons"><span
                                        onclick="login(undefined, true)">Authorize</span>
                            </li>
                            <?php if(Meta::getAdmlv($session->getName()) === Meta::ADMLV_ADMIN &&
                                ($session->getLogin()["opts"]->allowSu ?? false)) { ?>
                                <li class="loginbuttons">
                                    <span onclick='ajax("login.su", {data: {target: prompt("su")}, success: function() { window.location.reload(true); }})'><code>su</code></span>
                                </li>
                            <?php } ?>
                            <li class="nav-item loginbuttons">
                                <span onclick="location = getRelativeRootPath() + 'settings';">Settings</span>
                            </li>
                            <li class="nav-item loginbuttons"><span onclick="logout()">Logout</span></li>
                            <div class="avataricon">
                                <a target="_blank"
                                   href="https://github.com/<?= htmlspecialchars($session->getName()) ?>?tab=repositories">
                                    <img width="20" height="20"
                                         src="https://github.com/<?= htmlspecialchars($session->getName()) ?>.png"></a>
                            </div>
                        <?php } else { ?>
                            <li class="nav-item loginbuttons"><span onclick='login()'>Login with GitHub</span></li>
                            <li class="nav-item loginbuttons"><span onclick="login(undefined, true)">Custom Login</span>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </nav>
        </div>
        <?php if(!$session->tosHidden()) { ?>
            <div id="remindTos">
                <div class="alert alert-danger" align='center'>
                    <strong>30 Oct 2017 - Warning!</strong> A critical security update is available for <a href='<?= Meta::root() . 'p/DevTools' ?>'>DevTools</a>. Please update immediately.
                </div>
                <p>By continuing to use this site, you agree to the <a href='<?= Meta::root() ?>tos'>Terms of
                        Service</a> of this website, including usage of cookies.</p>
                <p><span class='action' onclick='hideTos()'>OK, Don't show this again</span></p>
            </div>
        <?php } ?>
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
                <li>&copy; <?= date("Y") ?> Poggit; some icons by Freepik from www.flaticon.com</li>
                <li id="online-user-count"></li>
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

    public static function queueJs(string $fileName) {
        self::$jsList[] = $fileName;
    }

    public static function includeJs(string $fileName, bool $async = false) {
        if(isset($_REQUEST["debug-include-assets-direct"]) || filesize(JS_DIR . $fileName . ".js") < 4096) {
            echo "<script>//$fileName.js\n";
            readfile(JS_DIR . $fileName . ".js");
            echo "</script>";
            return;
        }
        $noResCache = Meta::getSecret("meta.noResCache", true) ?? false;
        $prefix = "/" . ($noResCache ? substr(bin2hex(random_bytes(4)), 0, 7) : substr(Meta::$GIT_COMMIT, 0, 7));
        $src = Meta::root() . "js/{$fileName}.js{$prefix}";
        ?>
      <script type="text/javascript"<?= $async ? " async" : "" ?> src="<?= $src ?>"></script>
        <?php
    }

    public static function includeCss(string $fileName) {
        if(isset($_REQUEST["debug-include-assets-direct"]) || filesize(RES_DIR . $fileName . ".css") < 4096) {
            echo "<style>";
            readfile(RES_DIR . $fileName . ".css");
            echo "</style>";
            return;
        }
        $noResCache = Meta::getSecret("meta.noResCache", true) ?? false;
        $prefix = "/" . ($noResCache ? substr(bin2hex(random_bytes(4)), 0, 7) : substr(Meta::$GIT_COMMIT, 0, 7));
        $href = Meta::root() . "res/{$fileName}.css{$prefix}";
        ?>
      <link type="text/css" rel="stylesheet" href="<?= $href ?>">
        <?php
    }

    protected function param(string $name, array $array = null) {
        if($array === null) $array = $_REQUEST;
        if(!isset($array[$name])) $this->errorBadRequest("Missing parameter '$name'");
        return $array[$name];
    }
}
