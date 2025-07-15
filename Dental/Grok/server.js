const express = require('express');
const cors = require('cors');
const app = express();
const port = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());

// Mock data for MVP (replace with real API calls, e.g., Twitter API)
const mockSentimentData = [
  { topic: 'Patient Scheduling', sentiment: 'Negative', confidence: 78 },
  { topic: 'Cosmetic Dentistry', sentiment: 'Positive', confidence: 92 },
  { topic: 'Insurance Claims', sentiment: 'Negative', confidence: 65 },
  { topic: 'Oral Hygiene', sentiment: 'Positive', confidence: 85 }
];

const mockContentSuggestions = [
  { text: 'Did you know regular cleanings prevent gum disease? Book today! #DentalHealth' },
  { text: 'Brighten your smile with our whitening services! #SmileBright' },
  { text: 'Ask us about our new pain-free fillings! #DentalCare' }
];

const mockCompetitorInsights = [
  { competitor: 'Smile Dental', strategy: 'Free Checkup Campaigns', engagement: 'High' },
  { competitor: 'Bright Smiles', strategy: 'Community Events', engagement: 'Medium' },
  { competitor: 'Healthy Teeth Clinic', strategy: 'Social Media Ads', engagement: 'Low' }
];

// Sentiment Analysis Endpoint
app.get('/api/sentiment', (req, res) => {
  // Placeholder: Replace with real sentiment analysis (e.g., Hugging Face API)
  res.json(mockSentimentData);
});

// Content Generation Endpoint
app.get('/api/content', (req, res) => {
  // Placeholder: Replace with real content generation (e.g., OpenAI API)
  res.json(mockContentSuggestions);
});

// Competitor Insights Endpoint
app.get('/api/competitors', (req, res) => {
  // Placeholder: Replace with real competitor analysis (e.g., Twitter API data)
  res.json(mockCompetitorInsights);
});

// Error handling for invalid routes
app.use((req, res) => {
  res.status(404).json({ error: 'Route not found' });
});

// Start server
app.listen(port, () => {
  console.log(`Server running on port ${port}`);
});