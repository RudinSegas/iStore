# Portfolio walkthrough

Demonstrate the Hebrew catalog, category and price filtering, guest cart, registration, demo checkout and user-specific order history. Emphasize that no payment is taken.

Ask the assistant: "מה ההבדל בין RAM ל־SSD?", then "מחשב לפיתוח עם Docker עד 5000 שקל", then "השווה את אלה ומה חסר במפרט?". Explain that PHP executes prepared MySQL retrieval; Gemini receives limited catalog context and generates reasoning. Fact placeholders are resolved from the database, product cards are server-owned, and the model never writes SQL.

Discuss the tradeoffs honestly: deterministic retrieval suits this small catalog, qualitative AI advice can be wrong, free API quotas can be exhausted, and shared-host connectivity must be tested on the actual account. Show mocked failure tests and actual local API results in TEST_REPORT.md. Do not show private configuration or API keys during a presentation.
