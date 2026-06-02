> ⚠️ **HISTORICAL — DO NOT USE FOR NEW WORK**
>
> ไฟล์นี้คือ original handoff bundle (พ.ค. 2569) ก่อนเริ่ม implement Laravel app.
> ปัจจุบัน Laravel implementation ได้พัฒนาฟีเจอร์เกินกว่า prototype นี้แล้ว ~3 เท่า
> และมี dark mode, role-based UI, audit log, search ฯลฯ ที่ไม่อยู่ที่นี่.
>
> 👉 **ดู `../README.md` สำหรับ current design system** (DESIGN_SYSTEM.md, COMPONENT_INVENTORY.md, screenshots/).
>
> เก็บไว้เป็นบันทึก historical เท่านั้น.

---

# CODING AGENTS: READ THIS FIRST

This is a **handoff bundle** from Claude Design (claude.ai/design).

A user mocked up designs in HTML/CSS/JS using an AI design tool, then exported this bundle so a coding agent can implement the designs for real.

## What you should do — IMPORTANT

**Read the chat transcripts first.** There are 1 chat transcript(s) in `untitled/chats/`. The transcripts show the full back-and-forth between the user and the design assistant — they tell you **what the user actually wants** and **where they landed** after iterating. Don't skip them. The final HTML files are the output, but the chat is where the intent lives.

**Read `untitled/project/Price Reference System.html` in full.** The user had this file open when they triggered the handoff, so it's almost certainly the primary design they want built. Read it top to bottom — don't skim. Then **follow its imports**: open every file it pulls in (shared components, CSS, scripts) so you understand how the pieces fit together before you start implementing.

**If anything is ambiguous, ask the user to confirm before you start implementing.** It's much cheaper to clarify scope up front than to build the wrong thing.

## About the design files

The design medium is **HTML/CSS/JS** — these are prototypes, not production code. Your job is to **recreate them pixel-perfectly** in whatever technology makes sense for the target codebase (React, Vue, native, whatever fits). Match the visual output; don't copy the prototype's internal structure unless it happens to fit.

**Don't render these files in a browser or take screenshots unless the user asks you to.** Everything you need — dimensions, colors, layout rules — is spelled out in the source. Read the HTML and CSS directly; a screenshot won't tell you anything they don't.

## Bundle contents

- `untitled/README.md` — this file
- `untitled/chats/` — conversation transcripts (read these!)
- `untitled/project/` — the `ระบบจัดการข้อมูลราคากลาง` project files (HTML prototypes, assets, components)
