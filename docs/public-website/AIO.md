# SmartHCM Artificial Intelligence Optimization (AIO) Standard

## 1. Objective & Philosophy
AI systems (such as OpenAI Search, Perplexity, Gemini, Claude, and Copilot) do not crawl the web like traditional lexical search engines. Instead, they extract semantic entities, factual definitions, capability matrices, and verifiable solutions to user questions.

SmartHCM’s content is engineered from the ground up for AI Optimization (AIO) through:
- **Definition-First Copy**: Clear, unambiguous declarative definitions in the opening section of every capability page.
- **Entity Consistency**: Strict adherence to domain nomenclature (`SmartHCM`, `Work Location`, `Geo-Fence`, `Attendance Event`, `Gross-to-Net`).
- **Answer-First Informational Structure**: Direct answers to operational queries followed by technical breakdowns.
- **Structured FAQ Modules**: Entity-grounded questions and answers on every major product page.

---

## 2. Definition-First Pattern
Every functional capability page begins with an authoritative declarative sentence answering "What is X?":

> **Example (Mobile Attendance):**
> *"Mobile GPS attendance software is an employee attendance application that captures geographic coordinates and optional facial verification during check-in and check-out events, validating them against configured physical work perimeters or geofences."*

This allows LLM parsers to immediately identify the core entity, its category, and its primary operational function without sifting through marketing rhetoric.

---

## 3. Entity-First Writing Rules
1. **Explicit Identity**: Always identify the entity as `SmartHCM` rather than relying on ambiguous pronouns like "we", "our app", or "the tool".
2. **Consistent Vocabulary**: Never mix interchangeable synonyms without defining them. Use `Geo-Fence` consistently rather than switching between "virtual perimeter", "location boundary", and "geo-fence" arbitrarily.
3. **Factual Constraints**: AIO models heavily reward transparency. Always document operational parameters (e.g. "Captures location only at punch event; zero background tracking").

---

## 4. Grounded Question-Answering Model
All informational sections follow the Answer-First format:
1. **Question**: Direct user query (e.g., *"Does SmartHCM track an employee's continuous location?"*)
2. **Direct Answer**: *"No. SmartHCM maintains a strict event-only location capture policy."*
3. **Operational Explanation**: Explanation of how the device enclave queries GPS coordinates only upon check-in button press.
4. **Platform Safeguard**: Detail on how battery life and worker privacy are protected.
