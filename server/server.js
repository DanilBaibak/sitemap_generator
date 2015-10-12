var app         = require('express')(),
    querystring = require('querystring'),
    httpMy      = require('http'),
    url         = require('url'),
    http        = require('http').Server(app),
    io          = require('socket.io')(http),
    config      = require('./config.js');

io.on('connection', function(socket) {
    /**
     * Send list of the settings to the server
     */
    socket.on('create sitemap', function(data){
        //prepare data
        data = querystring.stringify(data);
        //settings for send data
        var options = {
            host: config.host,
            port: 80,
            path: '/create_site_map',
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Content-Length': data.length
            }
        };

        //run request
        var post_req = httpMy.request(options, function(res){
            var str = '';

            //handle response from server
            if (res.statusCode == 200) {
                res.on('data', function (chunk) {
                    str += chunk;
                });

                res.on('end', function () {
                    //send response from server after generation of the XML was finished
                    io.emit('generation finished', str);
                });
            } else {
                io.emit('generation finished', {status: false, message: 'Server return code ' + res.statusCode});
            }
        });

        post_req.write(data);
        post_req.end();
    });
});

/**
 * Get from server number of the unique links that were found
 * and send it to client
 */
app.get('/links_number', function(req, res) {
    var urlParts = url.parse(req.url, true),
        query    = urlParts.query;

    io.emit('links number', query.links_number);
    res.end();
});

/**
 * Get from server memory usage
 * and send it to client
 */
app.get('/memory_usage', function(req, res) {
    var urlParts = url.parse(req.url, true),
        query    = urlParts.query;

    io.emit('memory usage', query.memory);
    res.end();
});

/**
 * Get from server depth of the searching
 * and send it to client
 */
app.get('/links_depth', function(req, res) {
    var urlParts = url.parse(req.url, true),
        query    = urlParts.query;

    io.emit('links depth', query.depth);
    res.end();
});

/**
 * Get from server the last link that was found
 * and send it to client
 */
app.get('/current_link', function(req, res) {
    var urlParts = url.parse(req.url, true),
        query    = urlParts.query;

    io.emit('current link', query.link);
    res.end();
});

http.listen(9090);