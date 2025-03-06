"use strict";
const rp = require("request-promise");

class Socket {
  constructor(socket) {
    console.log("Constructor initialized");
    this.io = socket;
    // this.baseurl = "https://hunkr.itechnolabs.co.in/";
    this.baseurl = "https://martease.in/";
// this.baseurl = "http://127.0.0.1:8001/"

    // this.baseurl = "http://192.168.1.100:8000/";
  }

  socketEvents() {
    this.io.on("connection", (socket) => {
      console.log("Client connected:", socket.id);

      socket.on("authenticate", async (data, callback) => {
        try {
          const user_token = data.token;
          const userSocketId = socket.id;
          const options = {
            uri: `${this.baseurl}api/v1/user/socket/register`,
            headers: {
              Authorization: user_token,
            },
            body: {
              socket_id: userSocketId,
            },
            method: "POST",
            json: true,
          };

          const res = await rp(options);
          socket.broadcast.emit("user_online", res);
          callback(res);
        } catch (err) {
          const customResponse = {
            success: false,
            status_code: err.statusCode,
            message: err.error.message,
            error: err.error.error
          };
          callback(customResponse);
        }
      });

      socket.on("send_message", async (data, callback) => {
        try {
          const token = data.token;
          const to_id = data.to_id;
          const message = data.message;
          const unique_id = data.unique_id;
          const message_count = data.message_count
          const options = {
            uri: `${this.baseurl}api/v1/user/socket/send-message`,
            headers: {
              Authorization: token, 
            },
            body: {
              to_id: to_id,
              message: message,
              unique_id: unique_id,
              message_count: message_count
            },
            method: "POST",
            json: true,
          };
          const res = await rp(options);
          socket
            .to(res.data.to_socket)
            .emit("new_message_received", res, (ack) => {
              console.log(
                "Server acknowledgment for new_message_received:",
                res
              );
            });
          callback(res);
        } catch (err) {
          const customResponse = {
            success: false,
            status_code: err.statusCode,
            message: err.error.message,
            error: err.error.error
          };
          callback(customResponse);
        }
      });

      socket.on("new_message_received", (response, callback) => {
        console.log("asdfasdfasdfasdfsad");
        console.log(response);
      });

      socket.on("send_file", async (data, callback) => {
        console.log(data);
        socket
          .to(data.data.to_socket)
          .emit("new_message_received", data, (ack) => {
            console.log(
              "Server acknowledgment for new_message_received:",
              data
            );
          });
      });

      socket.on("share_post", async (data, callback) => {
        console.log(data.data.to_socket);
        socket
          .to(data.data.to_socket)
          .emit("new_message_received", data, (ack) => {
            console.log(
              "Server acknowledgment for new_message_received:",
              data
            );
          });
      });

      socket.on("share_story", async (data, callback) => {
        console.log(data);
        socket
          .to(data.data.to_socket)
          .emit("new_message_received", data, (ack) => {
            console.log(
              "Server acknowledgment for new_message_received:",
              data
            );
          });
      });

      socket.on("read_message", async (data, callback) => {
        try {
          const token = data.token;
          const message_id = data.message_id;
          const options = {
            uri: `${this.baseurl}api/v1/user/socket/read-message`,
            headers: {
              Authorization: token, // No need for `${}`
            },
            body: {
              message_id: message_id,
            },
            method: "POST",
            json: true,
          };

          const res = await rp(options);
          socket.to(res.data.from_user).emit("read_message", res);
          socket.to(res.data.to_socket).emit("message_readed", res, (ack) => {
            console.log("Server acknowledgment for message Readed:", res);
          });
          callback(res);
        } catch (err) {
          const customResponse = {
            status_code: err.statusCode,
            message: err.error.message,
            data: err.error.error,
          };
          callback(customResponse);
        }
      });

      socket.on("message_deleted", async (data, callback) => {
        socket
          .to(data.data.to_socket)
          .emit("message_deleted_listner", data, (ack) => {
            console.log("Server acknowledgment for message Readed:", data);
          });
      });

      socket.on("chat_deleted", async (data, callback) => {
        socket
          .to(data.data.to_socket)
          .emit("chat_deleted_listner", data, (ack) => {
            console.log("Server acknowledgment for message Readed:", data);
          });
      });

      socket.on("read_all_message", async (data, callback) => {
        try {
          const token = data.token;
          const chat_id = data.chat_id;
          const type = 1;
          const options = {
            uri: `${this.baseurl}api/v1/user/socket/read-all-message`,
            headers: {
              Authorization: token, // No need for `${}`
            },
            body: {
              chat_id: chat_id,
              type: type,
            },
            method: "POST",
            json: true,
          };

          const res = await rp(options);
          socket.to(res.data.from_socket).emit("read_all_message", res);
          socket
            .to(res.data.to_socket)
            .emit("all_message_readed", res, (ack) => {
              console.log("Server acknowledgment for message Readed:", res);
            });
          callback(res);
        } catch (err) {
          const customResponse = {
            status_code: err.statusCode,
            message: err.error.message,
            data: err.error.error,
          };
          callback(customResponse);
        }
      });

      socket.on("message_readed", (response, callback) => {});

      socket.on("reply_message", async (data, callback) => {
        try {
          const token = data.token;
          const message_id = data.message_id;
          const message = data.message;
          const to_id = data.to_id;
          const unique_id = data.unique_id;
          const options = {
            uri: `${this.baseurl}api/v1/user/socket/reply-message`,
            headers: {
              Authorization: token, // No need for `${}`
            },
            body: {
              message_id: message_id,
              message: message,
              to_id: to_id,
              unique_id: unique_id,
            },
            method: "POST",
            json: true,
          };

          const res = await rp(options);
          socket.to(res.data.from_socket).emit("reply_message", res);
          socket
            .to(res.data.from_socket)
            .emit("new_message_received", res, (ack) => {
              console.log("Server acknowledgment for message Readed:", res);
            });
          socket
            .to(res.data.to_socket)
            .emit("new_message_received", res, (ack) => {
              console.log("Server acknowledgment for message Readed:", res);
            });
          callback(res);
        } catch (err) {
          const customResponse = {
            status_code: err.statusCode,
            message: err.error.message,
            data: err.error.error,
          };
          callback(customResponse);
        }
      });

      socket.on("forword_message", async (data, callback) => {
        try {
          const token = data.token;
          const message_id = data.message_id;
          const to_id = data.to_ids;
          const type = data.type;
          const unique_id = data.unique_id;
          const options = {
            uri: `${this.baseurl}api/v1/user/socket/forword-message`,
            headers: {
              Authorization: token,
            },
            body: {
              message_id: message_id,
              to_ids: to_id,
              unique_id: unique_id,
              type: type,
            },
            method: "POST",
            json: true,
          };

          const res = await rp(options);
          if (res.success && Array.isArray(res.data)) {
            const mappedData = res.data.map(item => {
              socket.to(item.original.data.to_socket).emit("new_message_received", item.original, (ack) => {
                console.log("Server acknowledgment for message Readed:", item.original);
              });
              callback(item.original);
            });
          } else {
            console.log("Invalid response format");
          }        
        } catch (err) {
          const customResponse = {
            status_code: err.statusCode,
            message: err.error.message,
            data: err.error.error,
          };
          callback(customResponse);
        }
      });

      socket.on("close_socket", async (data, callback) => {
        try {
          const user_token = data.token;

          const options = {
            uri: `${this.baseurl}api/v1/user/socket/disconnect`,
            headers: {
              Authorization: user_token,
            },
            body: {},
            method: "POST",
            json: true,
          };

          const res = await rp(options);
          socket.broadcast.emit("user_offline", res);
          callback(res);
        } catch (err) {
          const customResponse = {
            status_code: err.statusCode,
            message: err.error.message,
            data: err.error.error,
          };
          callback(err);
        }
      });
      // message liked
      socket.on("message_liked", async (data, callback) => {
        socket
          .to(data.data.to_socket)
          .emit("message_liked_listner", data, (ack) => {
            console.log("Server acknowledgment for message Readed:", data);
          });
      });

      //community Messages
      socket.on("community_send_message", async (data, callback) => {
        console.log(data);
        try {
          const token = data.token;
          const community_id = data.community_id;
          const message = data.message;
          const unique_id = data.unique_id;
          const options = {
            uri: `${this.baseurl}api/v1/user/community/chat/send-message`,
            headers: {
              Authorization: token,
            },
            body: {
              community_id: community_id,
              message: message,
              unique_id: unique_id,
            },
            method: "POST",
            json: true,
          };

          const res = await rp(options);
          socket.to(res.data.from_socket).emit("community_send_message", res);

          socket.broadcast.emit("community_new_message", res, (ack) => {
            console.log("Server acknowledgment for new_message_received:", res);
          });
          callback(res);
        } catch (err) {
          const customResponse = {
            status_code: err.statusCode,
            message: err.error.message,
            data: err.error.error,
          };
          callback(customResponse);
        }
      });

      socket.on("community_reply_message", async (data, callback) => {
        try {
          const token = data.token;
          const message_id = data.message_id;
          const message = data.message;
          const community_id = data.community_id;
          const unique_id = data.unique_id;
          const options = {
            uri: `${this.baseurl}api/v1/user/community/chat/reply-message`,
            headers: {
              Authorization: token, // No need for `${}`
            },
            body: {
              message_id: message_id,
              message: message,
              community_id: community_id,
              unique_id: unique_id,
            },
            method: "POST",
            json: true,
          };

          const res = await rp(options);
          socket.to(res.data.from_socket).emit("community_reply_message", res);

          this.io.emit("community_new_message", res, (ack) => {
            console.log("Server acknowledgment for new_message_received:", res);
          });
          callback(res);
        } catch (err) {
          const customResponse = {
            status_code: err.statusCode,
            message: err.error.message,
            data: err.error.error,
          };
          callback(customResponse);
        }
      });

      socket.on("community_send_file", async (data, callback) => {
        console.log(data);
        socket.broadcast.emit("community_new_message", data, (ack) => {
          console.log("Server acknowledgment for community_new_message:", data);
        });
      });

      socket.on("community_message_delete", async (data, callback) => {
        socket.broadcast.emit("community_message_delete_listner", data, (ack) => {
          console.log("Server acknowledgment for new_message_received:", data);
        });
      });

      socket.on("community_message_liked", async (data, callback) => {
        socket.broadcast.emit("community_message_liked_listner", data, (ack) => {
          console.log("Server acknowledgment for new_message_received:", data);
        });
      });

      socket.on("community_read_message", async (data, callback) => {
        try {
          const token = data.token;
          const message_id = data.message_id;
          const options = {
            uri: `${this.baseurl}api/v1/user/community/chat/read-message`,
            headers: {
              Authorization: token, // No need for `${}`
            },
            body: {
              message_id: message_id,
            },
            method: "POST",
            json: true,
          };

          const res = await rp(options);
          socket.to(res.data.from_socket).emit("community_read_message", res);
          callback(res);
        } catch (err) {
          const customResponse = {
            status_code: err.statusCode,
            message: err.error.message,
            data: err.error.error,
          };
          callback(customResponse);
        }
      });

      //Help Support Chat Events
      socket.on("admin_connected", async (data, callback) => {
        try {
          const user_token = data.token;
          const userSocketId = socket.id;
          const options = {
            uri: `${baseurl}api/v1/admin/chat/checkauth`,
            headers: {
              Authorization: user_token,
            },
            body: {
              userSocketId,
            },
            method: "POST",
            json: true,
          };

          const res = await rp(options);
          socket.broadcast.emit("user_online", res);
          callback(res);
        } catch (err) {
          const customResponse = {
            status_code: err.statusCode,
            message: err.error.message,
            data: err.error.error,
          };
          callback(customResponse);
        }
      });

      socket.on("send_help_support_message", async (data, callback) => {
        try {
          const token = data.token;
          const help_support_id = data.help_support_id;
          const message = data.message;
          const unique_id = data.unique_id;
          const options = {
            uri: `${this.baseurl}api/v1/user/help-support-chat/send-message`,
            headers: {
              Authorization: token,
            },
            body: {
              help_support_id: help_support_id,
              message: message,
              unique_id: unique_id,
            },
            method: "POST",
            json: true,
          };

          const res = await rp(options);
          socket
            .to(res.data.from_socket)
            .emit("send_help_support_message", res);
          // socket
          //   .to(res.data.to_socket)
          //   .emit("message_from_help_support_user", res, (ack) => {
          //     console.log("message_from_help_support_user:", res);
          //   });
          socket.broadcast.emit(
            "message_from_help_support_user",
            res,
            (ack) => {
              console.log(
                "Acknowledgment from server for the message_from_help_support_user:",
                ack
              );
            }
          );

          callback(res);
        } catch (err) {
          const customResponse = {
            status_code: err.statusCode,
            message: err.error.message,
            data: err.error.error,
          };
          callback(customResponse);
        }
      });

      socket.on("help_support_message_emit", (data, ackCallback) => {
        socket
          .to(data.data.to_socket)
          .emit("help_support_new_message", data, (ackResponse) => {
            console.log(data.data.to_socket);
            if (ackResponse) {
              console.log("Recipient acknowledgment:", ackResponse);
            }
          });

        if (ackCallback) {
          ackCallback({
            status: "success",
            message: "Message processed successfully",
          });
        }
      });

      socket.on("end_help_support_session", (data, ackResponse) => {
        socket.to(data.to_socket).emit("help_support_session_ended", data, (response) => {
            if (response) {
                console.log("Recipient acknowledgment for session end:", response);
            }
        });
    
        if (ackResponse) {
            ackResponse({ status: 'success', message: 'Session end request processed.' });
        }
      });

      //Group Chat Events 
      socket.on("group_send_message", async (data, callback) => {
        console.log(data);
        try {
          const token = data.token;
          const group_id = data.group_id;
          const message = data.message;
          const unique_id = data.unique_id;
          const options = {
            uri: `${this.baseurl}api/v1/user/groups/send-message`,
            headers: {
              Authorization: token,
            },
            body: {
              group_id: group_id,
              message: message,
              unique_id: unique_id,
            },
            method: "POST",
            json: true,
          };
          const res = await rp(options);
          socket.broadcast.emit("group_new_message", res, (ack) => {
            console.log("Server acknowledgment for new_message_received:", res);
          });
          callback(res);
        } catch (err) {
          const customResponse = {
            status_code: err.statusCode,
            message: err.error.message,
            data: err.error.error,
          };
          callback(customResponse);
        }
      });

      socket.on("group_send_file", async (data, callback) => {
        console.log(data);
        socket.broadcast.emit("group_new_message", data, (ack) => {
          console.log("Server acknowledgment for community_new_message:", data);
        });
      });

      socket.on("group_message_liked", async (data, callback) => {
        socket.broadcast.emit("group_message_liked_listner", data, (ack) => {
          console.log("Server acknowledgment for new_message_received:", data);
        });
      });

      socket.on("group_message_unliked", async (data, callback) => {
        socket.broadcast.emit("group_message_unliked_listner", data, (ack) => {
          console.log("Server acknowledgment for Message Unliked:", data);
        });
      });

      socket.on("group_read_message", async (data, callback) => {
        try {
          const token = data.token;
          const message_id = data.message_id;
          const options = {
            uri: `${this.baseurl}api/v1/user/groups/read-message`,
            headers: {
              Authorization: token, // No need for `${}`
            },
            body: {
              message_id: message_id,
            },
            method: "POST",
            json: true,
          };

          const res = await rp(options);
          socket.to(res.data.from_socket).emit("group_read_message", res);
          callback(res);
        } catch (err) {
          const customResponse = {
            status_code: err.statusCode,
            message: err.error.message,
            data: err.error.error,
          };
          callback(customResponse);
        }
      });

      socket.on("group_reply_message", async (data, callback) => {
        try {
          const token = data.token;
          const message_id = data.message_id;
          const message = data.message;
          const group_id = data.group_id;
          const unique_id = data.unique_id;
          const options = {
            uri: `${this.baseurl}api/v1/user/groups/reply-message`,
            headers: {
              Authorization: token, // No need for `${}`
            },
            body: {
              message_id: message_id,
              message: message,
              group_id: group_id,
              unique_id: unique_id,
            },
            method: "POST",
            json: true,
          };

          const res = await rp(options);
          socket.to(res.data.from_socket).emit("group_reply_message", res);

          socket.broadcast.emit("group_new_message", res, (ack) => {
            console.log("Server acknowledgment for new_message_received:", res);
          });
          callback(res);
        } catch (err) {
          const customResponse = {
            status_code: err.statusCode,
            message: err.error.message,
            data: err.error.error,
          };
          callback(customResponse);
        }
      });

      socket.on("group_message_deleted", async (data, callback) => {
        socket.broadcast.emit("group_message_deleted_listner", data, (ack) => {
          console.log("Server acknowledgment for new_message_received:", data);
        });
      });

    });
  }

  socketConfig() {
    this.socketEvents();
  }
}

module.exports = Socket;