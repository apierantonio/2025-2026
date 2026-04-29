import { z } from 'zod';

/**
 * Placeholder schema for project metadata. Acts as the scaffolding
 * anchor so downstream packages can verify workspace linkage.
 *
 * Real domain schemas (blocks, bindings, generator manifests, etc.)
 * will be added in subsequent features and will live alongside this file.
 */
export const ProjectMetadataSchema = z.object({
  name: z.string().min(1),
});

export type ProjectMetadata = z.infer<typeof ProjectMetadataSchema>;
