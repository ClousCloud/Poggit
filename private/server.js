"use strict";
var express = require("express");
var path = require("path");
var logger = require("morgan");
var body_parser = require("body-parser");
var cookie_parser = require("cookie-parser");
var serve_favicon = require("serve-favicon");
var compression = require("compression");
var ui_router_1 = require("./ui/ui.router");
var cookies_app_1 = require("./session/cookies.app");
var res_router_1 = require("./res/res.router");
var version_1 = require("./version");
var csrf_router_1 = require("./session/csrf.router");
var tokens_1 = require("./session/tokens");
var authFlow_router_1 = require("./session/auth/authFlow.router");
var consts_1 = require("./consts");
var entry_router_1 = require("./webhook/entry.router");
var app = express();
app.set("views", path.join(version_1.POGGIT.INSTALL_ROOT, "views"));
app.set("view engine", "pug");
app.use(logger("dev"));
app.use(body_parser.urlencoded({ extended: false }));
app.use(cookie_parser());
app.use(compression({}));
app.use(function (req, res, next) {
    res.set("X-Powered-By", "Express/4, Poggit/" + version_1.POGGIT.VERSION);
    req.realIp = req.headers["cf-connecting-ip"] || req.connection.remoteAddress;
    next();
});
app.use(serve_favicon(path.join(version_1.POGGIT.INSTALL_ROOT, "res", "poggit.png")));
app.use("/res", res_router_1.res("res"));
app.use("/js", res_router_1.res("legacy"));
app.use("/ts", res_router_1.res("public"));
app.use("/gamma/webhook", entry_router_1.webhookRouter);
app.use(cookies_app_1.auth);
app.use("/gamma/flow", authFlow_router_1.authFlow);
app.use("/csrf", csrf_router_1.csrf);
app.use(ui_router_1.ui);
setInterval(tokens_1.cleanTokens, 10000);
setInterval(cookies_app_1.cleanSessions, 10000);
consts_1.initAppLocals(app.locals);
module.exports = app;
