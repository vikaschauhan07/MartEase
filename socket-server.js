const express = require("express");
const socketio = require("socket.io");
const bodyParser = require("body-parser");
const cors = require("cors");
const socketEvents = require("./socket/socket");
const http = require("http");
class Server {
  constructor() {
    this.port = process.env.PORT || 3002;
    this.host = "127.0.0.1"; 
    this.app = express();
    this.app.use(bodyParser.urlencoded({ extended: true }));
    this.app.use(bodyParser.json());
    const corsOptions = {
      origin: "*", 
      credentials: true,
      optionsSuccessStatus: 200,
      methods: ["GET", "POST"], 
      allowedHeaders: ["Content-Type"], 
    };
    this.app.use(cors(corsOptions));
    this.httpsServer = http.createServer(this.app);
    this.io = socketio(this.httpsServer, {
      cors: {
        origin: "*",
        methods: ["GET", "POST"],
        allowedHeaders: ["Content-Type"],
        credentials: true
      }
    });
  }
  includeRoutes() {
    new socketEvents(this.io).socketConfig();
  }
  appExecute() {
    this.includeRoutes();
    this.httpsServer.listen(this.port, this.host, () => {
      console.log(`Server running on https://${this.host}:${this.port}`);
    });
  }
}
const app = new Server();
app.appExecute();