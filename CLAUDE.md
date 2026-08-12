# Cubrel

## Code comments

When a comment is warranted (the WHY isn't obvious from the code), keep it to one brief sentence — not a multi-sentence writeup of the reasoning or implementation decisions behind it.

- One line, plain statement of the fact/intent. Not a paragraph, not a justification essay.
- If the same rationale applies at many call sites, write it once (top of the file, or the shared place the pattern originates) instead of repeating the same sentence verbatim at every occurrence.

Bad (real example from this repo, seen 8 times across two files):
```
// Hardcoded green, independent of --success-color — deferred.
```
repeated near-verbatim at every site, plus a 4-line paragraph elsewhere explaining why a checkmark stays a fixed color in both themes.

Good:
```
// Fixed regardless of theme — renders arbitrary color, not app chrome.
```
one sentence, written once per pattern.
