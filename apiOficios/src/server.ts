import "dotenv/config";
import express from "express";
import publishToqueue from "./publisher";

const PORT = process.env.PORT || 3000;

const app = express();
app.use(express.json());

app.post("/publish", async (req, res) => {
  const data = req.body;
  if (!data) {
    console.warn("Received publish request without data.");
    return res.status(400).json({ error: "Data is required" });
  }
  try {
    await publishToqueue(data);
    res.status(200).json({ status: "Message published successfully", data });
  } catch (error) {
    console.error("Error publishing message:", error);
    res.status(500).json({ error: "Failed to publish message" });
  }
});

app.listen(PORT, () => {
  console.log(`Server is running on port ${PORT}`);
});
