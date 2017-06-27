
/** PrivateConstructor: SASLSHA1
 *  SASL SCRAM SHA 1 authentication.
 */
Strophe.SASLSHA1 = function() {};

/* TEST:
 * This is a simple example of a SCRAM-SHA-1 authentication exchange
 * when the client doesn't support channel bindings (username 'user' and
 * password 'pencil' are used):
 *
 * C: n,,n=user,r=fyko+d2lbbFgONRv9qkxdawL
 * S: r=fyko+d2lbbFgONRv9qkxdawL3rfcNHYJY1ZVvWVs7j,s=QSXCR+Q6sek8bf92,
 * i=4096
 * C: c=biws,r=fyko+d2lbbFgONRv9qkxdawL3rfcNHYJY1ZVvWVs7j,
 * p=v0X8v3Bz2T0CJGbJQyF0X+HI4Ts=
 * S: v=rmF9pqV8S7suAoZWja4dJRkFsKQ=
 *
 */

Strophe.SASLSHA1.prototype = new Strophe.SASLMechanism("SCRAM-SHA-1", true, 40);

Strophe.SASLSHA1.test = function(connection) {
    return connection.authcid !== null;
};

Strophe.SASLSHA1.prototype.onChallenge = function(connection, challenge, test_cnonce) {
    var cnonce = test_cnonce || MD5.hexdigest(Math.random() * 1234567890);

    var auth_str = "n=" + connection.authcid;
    auth_str += ",r=";
    auth_str += cnonce;

    connection._sasl_data.cnonce = cnonce;
    connection._sasl_data["client-first-message-bare"] = auth_str;

    auth_str = "n,," + auth_str;

    this.onChallenge = function (connection, challenge)
    {
        var nonce, salt, iter, Hi, U, U_old, i, k;
        var clientKey, serverKey, clientSignature;
        var responseText = "c=biws,";
        var authMessage = connection._sasl_data["client-first-message-bare"] + "," +
            challenge + ",";
        var cnonce = connection._sasl_data.cnonce;
        var attribMatch = /([a-z]+)=([^,]+)(,|$)/;

        while (challenge.match(attribMatch)) {
            var matches = challenge.match(attribMatch);
            challenge = challenge.replace(matches[0], "");
            switch (matches[1]) {
                case "r":
                    nonce = matches[2];
                    break;
                case "s":
                    salt = matches[2];
                    break;
                case "i":
                    iter = matches[2];
                    break;
            }
        }

        if (nonce.substr(0, cnonce.length) !== cnonce) {
            connection._sasl_data = {};
            return connection._sasl_failure_cb();
        }

        responseText += "r=" + nonce;
        authMessage += responseText;

        salt = Base64.decode(salt);
        salt += "\x00\x00\x00\x01";

        Hi = U_old = SHA1.core_hmac_sha1(connection.pass, salt);
        for (i = 1; i < iter; i++) {
            U = SHA1.core_hmac_sha1(connection.pass, SHA1.binb2str(U_old));
            for (k = 0; k < 5; k++) {
                Hi[k] ^= U[k];
            }
            U_old = U;
        }
        Hi = SHA1.binb2str(Hi);

        clientKey = SHA1.core_hmac_sha1(Hi, "Client Key");
        serverKey = SHA1.str_hmac_sha1(Hi, "Server Key");
        clientSignature = SHA1.core_hmac_sha1(SHA1.str_sha1(SHA1.binb2str(clientKey)), authMessage);
        connection._sasl_data["server-signature"] = SHA1.b64_hmac_sha1(serverKey, authMessage);

        for (k = 0; k < 5; k++) {
            clientKey[k] ^= clientSignature[k];
        }

        responseText += ",p=" + Base64.encode(SHA1.binb2str(clientKey));

        return responseText;
    }.bind(this);

    return auth_str;
};

Strophe.Connection.prototype.mechanisms[Strophe.SASLSHA1.prototype.name] = Strophe.SASLSHA1;