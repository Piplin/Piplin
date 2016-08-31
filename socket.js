var jwt = require('jsonwebtoken');
var fs = require('fs');

require('dotenv').load();

var debug = (process.env.APP_DEBUG === 'true' || process.env.APP_DEBUG === true);

var Redis = require('ioredis');
var redis = new Redis({
    port: process.env.REDIS_PORT || 6379, 
    host: process.env.REDIS_HOST || '127.0.0.1', 
    db: process.env.REDIS_DATABASE || 0,
    password: process.env.REDIS_PASSWORD || null
});

if (/^https/i.test(process.env.SOCKET_URL)) {
    var ssl_conf = {
        key:  (process.env.SOCKET_SSL_KEY_FILE  ? fs.readFileSync(process.env.SOCKET_SSL_KEY_FILE)  : null),
        cert: (process.env.SOCKET_SSL_CERT_FILE ? fs.readFileSync(process.env.SOCKET_SSL_CERT_FILE) : null),
        ca:   (process.env.SOCKET_SSL_CA_FILE   ? fs.readFileSync(process.env.SOCKET_SSL_CA_FILE)   : null)
    };

    var app = require('https').createServer(ssl_conf, handler);
} else {
    var app = require('http').createServer(handler);
}

var io  = require('socket.io')(app);

app.listen(parseInt(process.env.SOCKET_PORT), function() {
    if (debug) {
        console.log('Server is running!');
    }
});

function handler(req, res) {
    res.writeHead(200);
    res.end('');
}

// Middleware to check the JWT
io.use(function(socket, next) {
    var decoded;

    if (debug) {
        console.log('Token - ' + socket.handshake.query.jwt);
    }

    try {
        decoded = jwt.verify(socket.handshake.query.jwt, process.env.JWT_SECRET);

        if (debug) {
            console.log(decoded);
        }
    } catch (err) {
        if (debug) {
            console.error(err);
        }

        next(new Error('Invalid token!'));
    }

    if (decoded) {
        // everything went fine - save userId as property of given connection instance
        socket.userId = decoded.data.userId;
        next();
    } else {
        // invalid token - terminate the connection
        next(new Error('Invalid token!'));
    }
});

io.on('connection', function(socket) {
    if (debug) {
        console.log('connection');
    }
});

redis.psubscribe('*', function(err, count) {
    if (debug) {
        console.log('psubscribe');
    }
});

redis.on('pmessage', function(subscribed, channel, message) {
    message = JSON.parse(message);


    if (message.event.indexOf('RestartSocketServer') !== -1) {

        if (debug) {
            console.log('Restart command received');
        }

        process.exit();
        return;
    }

    if (debug) {
        console.log('Message received from event ' + message.event + ' to channel ' + channel);
    }

    io.emit(channel + ':' + message.event, message.data);
});
