import * as express from "express"
import {NextFunction} from "express"
import * as path from "path"
import * as logger from "morgan"
import * as body_parser from "body-parser"
import * as cookie_parser from "cookie-parser"
import * as serve_favicon from "serve-favicon"
import * as compression from "compression"
import {ui} from "./ui/ui.router"
import {auth, cleanSessions} from "./session/cookies.app"
import {res} from "./res/res.router"
import {POGGIT} from "./version"
import {csrf} from "./session/csrf.router"
import {cleanTokens} from "./session/tokens"
import {MyRequest, MyResponse} from "./extensions"
import {authFlow} from "./session/auth/authFlow.router"
import {initAppLocals} from "./consts"
import {webhookRouter} from "./webhook/entry.router"

const app = express()
app.set("views", path.join(POGGIT.INSTALL_ROOT, "views"))
app.set("view engine", "pug")

// HTTP
app.use(logger("dev"))
app.use(body_parser.urlencoded({extended: false}))
app.use(cookie_parser())
app.use(compression({}))
app.use((req: MyRequest, res: MyResponse, next: NextFunction) =>{
	res.set("X-Powered-By", "Express/4, Poggit/" + POGGIT.VERSION)
	req.realIp = (req.headers["cf-connecting-ip"] as string | undefined) || (req.connection.remoteAddress as string)
	next()
})

// static
app.use(serve_favicon(path.join(POGGIT.INSTALL_ROOT, "res", "poggit.png")))
app.use("/res", res("res"))
app.use("/js", res("legacy"))
app.use("/ts", res("public"))

// non-auth API
app.use("/gamma/webhook", webhookRouter)

// session-dynamic
app.use(auth)
app.use("/gamma/flow", authFlow)
app.use("/csrf", csrf)
app.use(ui)

// tasks
setInterval(cleanTokens, 10000)
setInterval(cleanSessions, 10000)

initAppLocals(app.locals)

export = app
