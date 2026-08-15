const http = require("http");
const express = require("express");
const { createProxyMiddleware } = require("http-proxy-middleware");
const { Server } = require("socket.io");

const API_UPSTREAM = process.env.API_UPSTREAM || "http://127.0.0.1:8000";
const PORT = Number(process.env.GATEWAY_PORT || 5000);
const ORIGINS = (
  process.env.CORS_ALLOWED_ORIGINS ||
  "http://localhost:5173,http://127.0.0.1:5173"
)
  .split(",")
  .map((origin) => origin.trim())
  .filter(Boolean);

const app = express();
const server = http.createServer(app);

const io = new Server(server, {
  cors: {
    origin: ORIGINS,
    credentials: true,
  },
});

io.on("connection", (socket) => {
  socket.on("join_doctor", (doctorId) => {
    if (doctorId == null) return;
    socket.join(`doctor:${doctorId}`);
  });

  socket.on("join_user", (userId) => {
    if (userId == null) return;
    socket.join(`user:${userId}`);
  });

  socket.on("join_chat", (chatId) => {
    if (!chatId) return;
    socket.join(`chat:${chatId}`);
  });

  socket.on("update_appointments", (payload = {}) => {
    if (payload.doctorId == null) return;
    io.to(`doctor:${payload.doctorId}`).emit("update_slots", payload);
  });

  socket.on("add_chat", (messageTo) => {
    if (messageTo == null) return;
    io.to(`user:${messageTo}`).emit("new_chat");
  });

  socket.on("send_message", (msg = {}) => {
    if (!msg.chatId) return;
    io.to(`chat:${msg.chatId}`).emit("receive_message", msg);
  });

  socket.on("send_comment", (data = {}) => {
    if (data.post_id == null) return;
    io.emit(`receive_comment_${data.post_id}`, data);
  });

  socket.on("send_post", (data) => {
    io.emit("receive_post", data);
  });
});

app.use(
  createProxyMiddleware((pathname) => !pathname.startsWith("/socket.io"), {
    target: API_UPSTREAM,
    changeOrigin: false,
    ws: false,
    logLevel: "warn",
    onProxyReq(proxyReq, req) {
      const host = req.headers.host || "localhost:5000";
      proxyReq.setHeader("X-Forwarded-Host", host);
      proxyReq.setHeader("X-Forwarded-Proto", "http");
      proxyReq.setHeader("X-Forwarded-Port", String(PORT));
    },
  }),
);

server.listen(PORT, "0.0.0.0", () => {
  console.log(
    `Clinicify gateway listening on ${PORT}, proxy -> ${API_UPSTREAM}`,
  );
});
