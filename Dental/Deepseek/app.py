# app.py
from flask import Flask, render_template
import requests
import re
from datetime import datetime
import os

app = Flask(__name__)

# Configuration - Easy to modify without code changes
SOURCES = {
    "reddit": {
        "url": "https://www.reddit.com/r/Dentistry.json?limit=50",
        "params": {"limit": 50},
        "headers": {"User-Agent": "DentalPainPointFinder/1.0"}
    },
    "twitter": {
        "url": "https://api.rss2json.com/v1/api.json",
        "params": {"rss_url": "https://nitter.net/search/rss?f=tweets&q=%23DentalLife+OR+%23DentistProblems"}
    }
}

KEYWORDS = [
    "annoying", "frustrat", "problem", "issue", "hard", "difficult", 
    "waste of time", "pain", "hate", "struggle", "complicated",
    "insurance", "claim", "no-show", "supply", "ordering", "software",
    "scheduling", "cancel", "payment", "technology", "system down"
]

CATEGORIES = {
    "Scheduling": ["scheduling", "appointment", "cancel", "no-show"],
    "Insurance": ["insurance", "claim", "reimbursement"],
    "Technology": ["software", "system", "computer", "update", "technology"],
    "Operations": ["supply", "ordering", "inventory", "shipping"],
    "Financial": ["payment", "billing", "collection", "fee"],
    "Patient Issues": ["compliance", "hygiene", "education", "communication"]
}

def fetch_data():
    results = []
    
    # Reddit data
    try:
        reddit_response = requests.get(
            SOURCES["reddit"]["url"],
            headers=SOURCES["reddit"]["headers"],
            timeout=10
        )
        if reddit_response.status_code == 200:
            posts = reddit_response.json().get("data", {}).get("children", [])
            for post in posts:
                post_data = post.get("data", {})
                results.append({
                    "source": "reddit",
                    "title": post_data.get("title", ""),
                    "content": post_data.get("selftext", ""),
                    "author": post_data.get("author", ""),
                    "date": datetime.utcfromtimestamp(post_data.get("created_utc", 0)),
                    "url": f"https://reddit.com{post_data.get('permalink', '')}"
                })
    except Exception as e:
        print(f"Reddit Error: {str(e)}")
    
    # Twitter data via RSS
    try:
        twitter_response = requests.get(
            SOURCES["twitter"]["url"],
            params=SOURCES["twitter"]["params"],
            timeout=10
        )
        if twitter_response.status_code == 200:
            tweets = twitter_response.json().get("items", [])
            for tweet in tweets:
                results.append({
                    "source": "twitter",
                    "title": "",
                    "content": tweet.get("title", "").split(": ", 1)[-1],
                    "author": tweet.get("author", ""),
                    "date": datetime.strptime(tweet.get("pubDate", ""), "%a, %d %b %Y %H:%M:%S %Z"),
                    "url": tweet.get("link", "")
                })
    except Exception as e:
        print(f"Twitter Error: {str(e)}")
    
    return results

def analyze_posts(posts):
    categorized = {category: [] for category in CATEGORIES}
    uncategorized = []
    
    for post in posts:
        text = f"{post['title']} {post['content']}".lower()
        matched = False
        
        # Check for pain keywords
        if not any(re.search(rf"\b{kw}\b", text) for kw in KEYWORDS):
            continue
        
        # Categorize
        for category, terms in CATEGORIES.items():
            if any(re.search(rf"\b{term}\b", text) for term in terms):
                categorized[category].append(post)
                matched = True
                break
        
        if not matched:
            uncategorized.append(post)
    
    return categorized, uncategorized

@app.route('/')
def dashboard():
    raw_posts = fetch_data()
    categorized, uncategorized = analyze_posts(raw_posts)
    
    # Prepare stats for display
    stats = {
        "total_posts": len(raw_posts),
        "pain_posts": sum(len(v) for v in categorized.values()) + len(uncategorized),
        "last_updated": datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    }
    
    return render_template(
        "dashboard.html",
        stats=stats,
        categorized=categorized,
        uncategorized=uncategorized
    )

if __name__ == "__main__":
    app.run(debug=True)