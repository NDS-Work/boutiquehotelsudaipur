---
name: blog-body-generator
description: Generates bespoke luxury HTML body markup for Boutique Hotels Udaipur travel guides and blogs, with styled comparison tables, callout blocks, pros/cons cards, and clean typography.
---

# Blog Body HTML Generator Skill

Use this skill to convert raw article text, outlines, or markdown into production-ready HTML markup designed for the Boutique Hotels Udaipur platform (`boutiquehotelsudaipur.com`).

---

## Design System & Color Palette

All generated markup must match the Boutique Hotels Udaipur luxury Mewari aesthetic:

- **Primary Heading / Maroon**: `#4b1111`
- **Gold Accent / Border**: `#c9913d`
- **Deep Muted Gold / Subtitles**: `#634d31`
- **Body Text**: `#333333` or `#454545`
- **Light Warm Background**: `#faf7f2` or `#fdfbf7`
- **Table Header Tint**: `#f4ecdf`
- **Border Separators**: `#eee5d8` or `rgba(201, 145, 61, 0.25)`

---

## Required HTML Component Patterns

### 1. Lead Paragraph (Opening)
Every article must start with an engaging, larger-font lead paragraph:
```html
<p class="lead" style="font-size: 1.15rem; line-height: 1.8; color: #4b1111; font-weight: 500; margin-bottom: 24px;">
    [Opening hook explaining the traveler dilemma or core question]
</p>
```

### 2. Editorial Callout / Quote Box
For key takeaways, editorial notes, or travel tips:
```html
<div style="background: #fdfbf7; border-left: 4px solid #c9913d; padding: 18px 24px; margin: 30px 0; border-radius: 4px;">
    <p style="margin: 0; font-style: italic; color: #634d31;">
        [Key insight, takeaway, or editorial tip]
    </p>
</div>
```

### 3. Comparison & Summary Table
For quick-scan decision tables comparing areas, hotels, or budgets:
```html
<h2>Quick Summary: [Comparison Topic]</h2>
<div style="overflow-x: auto; margin: 24px 0;">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f4ecdf; color: #634d31;">
                <th style="padding: 12px 16px; text-align: left; border-bottom: 2px solid #c9913d;">Parameter</th>
                <th style="padding: 12px 16px; text-align: left; border-bottom: 2px solid #c9913d;">Recommendation</th>
                <th style="padding: 12px 16px; text-align: left; border-bottom: 2px solid #c9913d;">Best Suited For</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 12px 16px; border-bottom: 1px solid #eee5d8;"><strong>Item 1</strong></td>
                <td style="padding: 12px 16px; border-bottom: 1px solid #eee5d8;">Option A</td>
                <td style="padding: 12px 16px; border-bottom: 1px solid #eee5d8;">Description</td>
            </tr>
            <tr style="background: #faf7f2;">
                <td style="padding: 12px 16px; border-bottom: 1px solid #eee5d8;"><strong>Item 2</strong></td>
                <td style="padding: 12px 16px; border-bottom: 1px solid #eee5d8;">Option B</td>
                <td style="padding: 12px 16px; border-bottom: 1px solid #eee5d8;">Description</td>
            </tr>
        </tbody>
    </table>
</div>
```

### 4. Section Headings & Dividers
Between major thematic sections, use clean dividers:
```html
<hr style="border: 0; border-top: 1px solid #e7ded0; margin: 40px 0;">

<h2>1. [Area or Topic Title]</h2>
<p>[Detailed explanation]</p>

<h3>Best For:</h3>
<ul>
    <li>[Benefit 1]</li>
    <li>[Benefit 2]</li>
</ul>

<h3>What to Keep in Mind:</h3>
<ul>
    <li><strong>[Caveat/Tradeoff]:</strong> [Practical advice regarding access, noise, stairs, etc.]</li>
</ul>
```

### 5. Pro / Con or Highlight Badges
When comparing hotels or neighborhoods:
```html
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin: 20px 0;">
    <div style="background: #fdfbf7; border: 1px solid rgba(201, 145, 61, 0.25); border-radius: 6px; padding: 16px;">
        <h4 style="margin: 0 0 10px 0; color: #2e6930;">✓ The Highlights</h4>
        <ul style="margin: 0; padding-left: 18px;">
            <li>Point 1</li>
            <li>Point 2</li>
        </ul>
    </div>
    <div style="background: #fdfbf7; border: 1px solid rgba(201, 145, 61, 0.25); border-radius: 6px; padding: 16px;">
        <h4 style="margin: 0 0 10px 0; color: #8a3324;">⚠ Trade-offs to Consider</h4>
        <ul style="margin: 0; padding-left: 18px;">
            <li>Point 1</li>
            <li>Point 2</li>
        </ul>
    </div>
</div>
```

---

## Strict Rules & Content Fidelity (CRITICAL)

The AI/tool executing this skill MUST strictly adhere to the following rules:

1. **DO NOT REWRITE OR REPHRASE:** Preserve the author's original words, tone, and sentence phrasing verbatim. Never paraphrase, summarize, or "improve" the text unless explicitly requested.
2. **NO SKIPPING CONTENT:** Every single paragraph, bullet point, sentence, table row, caveat, and heading from the source input must be included in the output. Do not omit sections or condense explanations.
3. **NO ADDING UNSPECIFIED MARKETING FLUFF:** Do not invent calls to action, generic summaries, or boilerplate phrases that the user did not write.
4. **ONLY ENHANCE STRUCTURE & MARKUP:** The task is strictly formatting and presentation — converting raw text/markdown into clean, semantically structured HTML using the design system classes and inline styles above.
5. **INNER BODY MARKUP ONLY:** Never wrap output in `<html>`, `<head>`, `<body>`, or `<!DOCTYPE>` tags. Generate only the inner elements (`<p>`, `<h2>`, `<h3>`, `<ul>`, `<table>`, `<div>`) ready to paste into the blog body editor.

---

## Processing Workflow

When provided with an article draft:
1. **Analyze Structure**: Identify the opening lead paragraph, section headings, list items, comparison tables, and highlighted takeaways.
2. **Apply Design System**: Wrap existing sentences in the appropriate HTML components without changing a single word.
3. **Double Check Completeness**: Verify that 100% of the input text is present in the final HTML output.
4. **Output Pure HTML**: Deliver the HTML snippet inside a markdown code block so the admin can copy and paste directly into the **`</> HTML Source Code`** mode in the admin panel.
