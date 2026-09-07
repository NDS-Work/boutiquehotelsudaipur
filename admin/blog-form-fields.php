<?php
// Shared form fields for admin/blog-add.php and admin/blog-edit.php
$categories = getAllBlogCategories(false);
?>

<!-- Include Quill Stylesheet & Script -->
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>

<style>
/* Quill Editor customization to match admin dark theme */
.ql-toolbar.ql-snow {
    background: var(--surface2);
    border-color: var(--border);
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
}
.ql-toolbar.ql-snow .ql-stroke {
    stroke: var(--text);
}
.ql-toolbar.ql-snow .ql-fill {
    fill: var(--text);
}
.ql-toolbar.ql-snow .ql-picker {
    color: var(--text);
}
.ql-toolbar.ql-snow .ql-picker-options {
    background: var(--surface2);
    border-color: var(--border);
}
.ql-container.ql-snow {
    background: #0c0e0d;
    border-color: var(--border);
    border-bottom-left-radius: 4px;
    border-bottom-right-radius: 4px;
    color: #e0e6e0;
    font-family: 'DM Mono', monospace;
    font-size: 13px;
    min-height: 380px;
}
.ql-editor {
    min-height: 380px;
    line-height: 1.7;
}
.char-counter {
    font-size: 11px;
    color: var(--muted);
    text-align: right;
    margin-top: 3px;
}
.char-counter.good { color: var(--success); }
.char-counter.warn { color: var(--accent2); }
.char-counter.bad  { color: var(--danger); }
</style>

<div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start;">

    <!-- Left Column: Core Content & Rich Text Body -->
    <div>
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-title">Blog Content</div>

            <div class="form-group">
                <label>Blog Title *</label>
                <input type="text" id="blog_title" name="title" required placeholder="e.g. 10 Best Boutique Hotels in Udaipur Near Lake Pichola" value="<?php echo htmlspecialchars($formData['title'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Slug (URL key) *</label>
                <input type="text" id="blog_slug" name="slug" placeholder="e.g. 10-best-boutique-hotels-near-lake-pichola" value="<?php echo htmlspecialchars($formData['slug'] ?? ''); ?>">
                <div class="form-hint">Public URL: /blog/<span id="slug_preview"><?php echo htmlspecialchars($formData['slug'] ?? 'your-slug'); ?></span></div>
            </div>

            <div class="form-group">
                <label>Excerpt / Summary</label>
                <textarea name="excerpt" id="blog_excerpt" rows="3" placeholder="A short 1-2 sentence compelling summary for search results and social cards..."><?php echo htmlspecialchars($formData['excerpt'] ?? ''); ?></textarea>
                <div class="form-hint">Used in blog cards and defaults as meta description if empty.</div>
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label style="margin: 0;">Body Content (Rich Text &amp; HTML) *</label>
                    <button type="button" id="toggle-source-btn" class="btn btn-secondary" style="padding: 4px 10px; font-size: 12px; display: inline-flex; align-items: center; gap: 5px;">
                        <span>&lt;/&gt;</span> <span id="source-btn-text">HTML Source Code</span>
                    </button>
                </div>
                <!-- Quill Container -->
                <div id="quill-editor">
                    <?php echo $formData['content'] ?? ''; ?>
                </div>
                <!-- Raw HTML Source Textarea (Hidden by default) -->
                <textarea id="raw-html-editor" rows="22" style="display: none; width: 100%; font-family: monospace; font-size: 13px; line-height: 1.5; background: var(--surface2); color: var(--text); border: 1px solid var(--border); border-radius: 4px; padding: 12px;" placeholder="Paste raw HTML here..."><?php echo htmlspecialchars($formData['content'] ?? ''); ?></textarea>
                <!-- Hidden input that gets populated on form submit -->
                <input type="hidden" name="content" id="hidden_content">
                <div class="form-hint" id="editor-hint">Tip: Click <strong>&lt;/&gt; HTML Source Code</strong> above to paste tables, custom HTML cards, or embed codes directly.</div>
            </div>
        </div>

        <!-- SEO Metadata Card -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-title" style="display:flex; justify-content:space-between; align-items:center;">
                <span>Search Engine Optimization (SEO)</span>
                <small style="color:var(--accent); font-size:11px;">Google Snippet Preview</small>
            </div>

            <div class="form-group">
                <label>Meta Title</label>
                <input type="text" id="meta_title" name="meta_title" placeholder="Catchy title under 60 chars..." value="<?php echo htmlspecialchars($formData['meta_title'] ?? ''); ?>">
                <div id="title_counter" class="char-counter">0 / 60 characters</div>
            </div>

            <div class="form-group">
                <label>Meta Description</label>
                <textarea id="meta_description" name="meta_description" rows="3" placeholder="Engaging summary under 160 chars for Google search results..."><?php echo htmlspecialchars($formData['meta_description'] ?? ''); ?></textarea>
                <div id="desc_counter" class="char-counter">0 / 160 characters</div>
            </div>

            <div class="form-group">
                <label>Meta Keywords</label>
                <input type="text" name="meta_keywords" placeholder="boutique hotels, udaipur, lake pichola stays, luxury haveli" value="<?php echo htmlspecialchars($formData['meta_keywords'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Canonical URL</label>
                <input type="text" name="canonical_url" placeholder="https://boutiquehotelsudaipur.com/blog/..." value="<?php echo htmlspecialchars($formData['canonical_url'] ?? ''); ?>">
                <div class="form-hint">Leave blank to auto-use standard blog URL.</div>
            </div>

            <div class="form-group">
                <label>Open Graph (OG) Image URL</label>
                <input type="text" name="og_image" placeholder="Image URL when shared on WhatsApp, Facebook, Twitter..." value="<?php echo htmlspecialchars($formData['og_image'] ?? ''); ?>">
                <div class="form-hint">Defaults to Featured Image if empty.</div>
            </div>

            <details style="margin-top: 15px; background: var(--surface2); padding: 12px; border: 1px solid var(--border);">
                <summary style="cursor: pointer; font-weight: 500; color: var(--accent);">FAQ Schema Builder (JSON-LD)</summary>
                <div style="margin-top: 10px;">
                    <p style="color: var(--muted); font-size: 11px; margin-bottom: 8px;">Paste custom FAQ items JSON or leave blank. Example: <code>[{"q": "Are lake views available?", "a": "Yes, many boutique hotels offer stunning lake vistas."}]</code></p>
                    <textarea name="schema_faq_json" rows="3" placeholder='[{"q": "Question here?", "a": "Answer here."}]'><?php echo htmlspecialchars($formData['schema_faq_json'] ?? ''); ?></textarea>
                </div>
            </details>
        </div>
    </div>

    <!-- Right Column: Settings, Category & Media -->
    <div>
        <div class="card" style="position: sticky; top: 80px;">
            <div class="card-title">Publishing & Taxonomies</div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="published" <?php echo ($formData['status'] ?? 'published') === 'published' ? 'selected' : ''; ?>>Published</option>
                    <option value="draft" <?php echo ($formData['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                </select>
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category_id">
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo (int)$cat['id']; ?>" <?php echo (int)($formData['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-hint"><a href="/admin/blog-categories.php" target="_blank" style="color:var(--accent);">+ Manage Categories</a></div>
            </div>

            <div class="form-group">
                <label>Author</label>
                <input type="text" name="author" value="<?php echo htmlspecialchars($formData['author'] ?? 'Boutique Hotels Editorial Team'); ?>">
            </div>

            <div class="form-group">
                <label>Tags (Comma separated)</label>
                <input type="text" name="tags" placeholder="Lake Pichola, Luxury, Couples" value="<?php echo htmlspecialchars($formData['tags'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Publish Date</label>
                <input type="text" name="published_at" value="<?php echo htmlspecialchars(!empty($formData['published_at']) ? date('Y-m-d H:i', strtotime((string)$formData['published_at'])) : date('Y-m-d H:i')); ?>">
            </div>

            <div class="form-group">
                <label>Featured Image</label>
                <input type="file" id="featured_file_input" name="featured_image_file" accept="image/*" style="margin-bottom: 8px;">
                <input type="text" name="featured_image" id="featured_image_input" placeholder="Or enter image URL: /assets/... or https://..." value="<?php echo htmlspecialchars($formData['featured_image'] ?? ''); ?>">
                
                <div id="preview_container" style="margin-top: 10px; <?php echo empty($formData['featured_image']) ? 'display: none;' : ''; ?>">
                    <div style="font-size: 11px; color: var(--muted); margin-bottom: 4px;">Image Preview:</div>
                    <img id="featured_img_preview" src="<?php echo htmlspecialchars($formData['featured_image'] ?? ''); ?>" alt="Preview" style="width: 100%; height: 160px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; flex-direction: column; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: bold;">
                    <?php echo isset($isEdit) && $isEdit ? 'Update Article' : 'Save & Publish'; ?>
                </button>
                <a href="/admin/download-content-guide.php" class="btn btn-secondary" style="text-align: center; font-size: 12px;" title="Download Markdown template & content guide for writers">
                    📥 Download Content Skill (.md)
                </a>
                <a href="/admin/blogs.php" class="btn btn-secondary" style="text-align: center;">Cancel</a>
            </div>
        </div>
    </div>

</div>

<!-- Initialize Quill & Handlers -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var toolbarOptions = [
        [{ 'header': [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        ['blockquote', 'code-block'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'color': [] }, { 'background': [] }],
        ['link', 'image', 'video'],
        ['clean']
    ];

    var quill = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: 'Write your engaging travel guide or blog post here...',
        modules: {
            toolbar: toolbarOptions
        }
    });

    // Custom image upload handler directly within Quill
    quill.getModule('toolbar').addHandler('image', function() {
        var input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();

        input.onchange = function() {
            var file = input.files[0];
            if (!file) return;

            var formData = new FormData();
            formData.append('image', file);

            var range = quill.getSelection(true);
            quill.insertText(range.index, 'Uploading image...', 'italic', true);

            fetch('/admin/upload-image.php', {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                quill.deleteText(range.index, 18);
                if (data.success && data.url) {
                    quill.insertEmbed(range.index, 'image', data.url);
                } else {
                    alert('Upload failed: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(function(err) {
                quill.deleteText(range.index, 18);
                alert('Upload error: ' + err.message);
            });
        };
    });

    // Auto slug generator from title
    var titleInput = document.getElementById('blog_title');
    var slugInput = document.getElementById('blog_slug');
    var slugPreview = document.getElementById('slug_preview');

    titleInput.addEventListener('input', function() {
        if (!slugInput.dataset.manual) {
            var slug = titleInput.value.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .trim()
                .replace(/[\s_-]+/g, '-');
            slugInput.value = slug;
            slugPreview.textContent = slug || 'your-slug';
        }
    });

    slugInput.addEventListener('input', function() {
        slugInput.dataset.manual = 'true';
        slugPreview.textContent = slugInput.value || 'your-slug';
    });

    // Character counters for SEO
    function updateCounter(input, counter, optimal) {
        var len = input.value.length;
        counter.textContent = len + ' / ' + optimal + ' characters';
        counter.className = 'char-counter';
        if (len === 0) {
            counter.classList.add('muted');
        } else if (len <= optimal) {
            counter.classList.add('good');
        } else if (len <= optimal + 15) {
            counter.classList.add('warn');
        } else {
            counter.classList.add('bad');
        }
    }

    var metaTitle = document.getElementById('meta_title');
    var titleCounter = document.getElementById('title_counter');
    metaTitle.addEventListener('input', function() { updateCounter(metaTitle, titleCounter, 60); });
    updateCounter(metaTitle, titleCounter, 60);

    var metaDesc = document.getElementById('meta_description');
    var descCounter = document.getElementById('desc_counter');
    metaDesc.addEventListener('input', function() { updateCounter(metaDesc, descCounter, 160); });
    updateCounter(metaDesc, descCounter, 160);

    // Live Featured Image Preview Handler
    var fileInput = document.getElementById('featured_file_input');
    var urlInput = document.getElementById('featured_image_input');
    var previewContainer = document.getElementById('preview_container');
    var previewImg = document.getElementById('featured_img_preview');

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            var file = fileInput.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (urlInput) {
        urlInput.addEventListener('input', function() {
            var val = urlInput.value.trim();
            if (val) {
                previewImg.src = val;
                previewContainer.style.display = 'block';
            } else if (!fileInput.files.length) {
                previewContainer.style.display = 'none';
            }
        });
    }

    // Toggle between Visual Rich Text and Raw HTML Source
    var isHtmlMode = false;
    var toggleBtn = document.getElementById('toggle-source-btn');
    var btnText = document.getElementById('source-btn-text');
    var quillContainer = document.getElementById('quill-editor');
    var rawTextarea = document.getElementById('raw-html-editor');
    var quillToolbar = document.querySelector('.ql-toolbar');
    var editorHint = document.getElementById('editor-hint');

    toggleBtn.addEventListener('click', function() {
        if (!isHtmlMode) {
            // Switch to HTML Source Mode
            rawTextarea.value = quill.root.innerHTML;
            quillContainer.style.display = 'none';
            if (quillToolbar) quillToolbar.style.display = 'none';
            rawTextarea.style.display = 'block';
            btnText.textContent = 'Visual Rich Editor';
            toggleBtn.classList.remove('btn-secondary');
            toggleBtn.classList.add('btn-primary');
            editorHint.innerHTML = '<span style="color:var(--accent);">Source Code Mode active.</span> Paste your HTML markup freely here.';
            isHtmlMode = true;
        } else {
            // Switch back to Visual Editor
            quill.root.innerHTML = rawTextarea.value;
            rawTextarea.style.display = 'none';
            quillContainer.style.display = 'block';
            if (quillToolbar) quillToolbar.style.display = 'block';
            btnText.textContent = 'HTML Source Code';
            toggleBtn.classList.remove('btn-primary');
            toggleBtn.classList.add('btn-secondary');
            editorHint.innerHTML = 'Tip: Click <strong>&lt;/&gt; HTML Source Code</strong> above to paste tables, custom HTML cards, or embed codes directly.';
            isHtmlMode = false;
        }
    });

    // Sync HTML before submit
    var form = document.querySelector('form');
    form.addEventListener('submit', function() {
        if (isHtmlMode) {
            document.getElementById('hidden_content').value = rawTextarea.value;
        } else {
            document.getElementById('hidden_content').value = quill.root.innerHTML;
        }
    });
});
</script>
