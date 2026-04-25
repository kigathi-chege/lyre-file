# `lyre/file` Agent Guide

## Package Purpose
`lyre/file` owns media file records, polymorphic attachments, and file delivery utilities (stream/download), plus Filament/Livewire utilities for selecting and browsing files.

## What Belongs In This Package
- `File` and `Attachment` models and their CRUD APIs.
- File transport endpoints (`/api/files/stream/*`, `/api/files/download/*`).
- File-specific Filament resources/actions/components.

## What Does Not Belong Here
- Non-media content structures (pages/sections) owned by `lyre/content`.
- Taxonomy/facet logic owned by `lyre/facet`.

## Public API / Stable Contracts
- Route shapes for file CRUD/stream/download.
- `HasFile` concern behavior and attachment relationship expectations.
- File gallery component contract used by Filament fields.

## Internal Areas That May Change
- Internal storage/action implementation details preserving endpoint and relation behavior.

## Usage Rules
- Use `HasFile` trait on host models that need attachments.
- Use package routes for stream/download rather than duplicating custom endpoints when possible.
- Register `LyreFileFilamentPlugin` in panels requiring media management UI.

## Extension Rules
- Keep attachment relation semantics backward compatible.
- Any new file metadata behavior should preserve existing serialized keys unless versioned.
- Avoid embedding app-specific storage assumptions; keep drivers configurable.

## Testing Requirements
- Validate file upload/listing endpoints, stream/download, and attachment relation reads.
- Validate Filament resource/component loading in a panel.

## Docs To Update When This Package Changes
- Root [AGENTS.md](/Users/chegekigathi/Projects/packages/lyre-packages/AGENTS.md)
- [docs/package-responsibilities.md](/Users/chegekigathi/Projects/packages/lyre-packages/docs/package-responsibilities.md)
- `packages/file/README.md`
