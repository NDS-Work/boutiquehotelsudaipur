<?php
$locations = adminVenueLocationOptions();
$priceRanges = adminVenuePriceOptions();
$capacities = adminVenueCapacityOptions();
$venueTypes = adminVenueTypeOptions();
?>

<div class="card">
    <div class="card-title">Basic Info</div>
    <div class="form-grid">
        <div class="form-group">
            <label>Venue Name *</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($formData['name'] ?? ''); ?>" required oninput="autoSlug(this)">
        </div>
        <div class="form-group">
            <label>Slug *</label>
            <input type="text" name="slug" id="slug" value="<?php echo htmlspecialchars($formData['slug'] ?? ''); ?>" required>
            <div class="form-hint">URL-friendly: taj-lake-palace</div>
        </div>
        <div class="form-group">
            <label>Location Label *</label>
            <?php
            $currentLabel = $formData['location_label'] ?? '';
            $labelInList = false;
            foreach ($locations as $location) {
                if ($location['label'] === $currentLabel) { $labelInList = true; break; }
            }
            ?>
            <select name="location_label" id="location_label_select"
                onchange="(function(s){
                    var inp = document.getElementById('location_label_custom');
                    var wrap = document.getElementById('location_label_custom_wrap');
                    if (s.value === '__new__') {
                        wrap.style.display = 'block';
                        inp.setAttribute('name', 'location_label');
                        inp.required = true;
                        s.removeAttribute('name');
                    } else {
                        wrap.style.display = 'none';
                        inp.removeAttribute('name');
                        inp.required = false;
                        inp.value = '';
                        s.name = 'location_label';
                    }
                })(this)" <?php echo $labelInList || $currentLabel === '' ? 'required' : ''; ?>>
                <option value="">- Select -</option>
                <?php foreach ($locations as $location): ?>
                <option value="<?php echo htmlspecialchars($location['label']); ?>" <?php echo $currentLabel === $location['label'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($location['label']); ?>
                </option>
                <?php endforeach; ?>
                <?php if ($currentLabel !== '' && !$labelInList): ?>
                <option value="<?php echo htmlspecialchars($currentLabel); ?>" selected><?php echo htmlspecialchars($currentLabel); ?></option>
                <?php endif; ?>
                <option value="__new__">+ Add new locality...</option>
            </select>
            <div id="location_label_custom_wrap" style="margin-top:6px;display:none;">
                <input type="text" id="location_label_custom"
                    placeholder="Enter new locality name" style="width:100%;">
            </div>
        </div>
        <div class="form-group">
            <label>City</label>
            <input type="text" name="city_name" value="<?php echo htmlspecialchars($formData['city_name'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>State</label>
            <input type="text" name="state_name" value="<?php echo htmlspecialchars($formData['state_name'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Country Code</label>
            <input type="text" name="country_code" maxlength="2" value="<?php echo htmlspecialchars($formData['country_code'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Distance Text</label>
            <input type="text" name="distance_text" value="<?php echo htmlspecialchars($formData['distance_text'] ?? ''); ?>" placeholder="e.g. 2 km from City Palace">
        </div>
        <div class="form-group">
            <label>Latitude</label>
            <input type="number" step="0.000001" name="latitude" value="<?php echo htmlspecialchars($formData['latitude'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Longitude</label>
            <input type="number" step="0.000001" name="longitude" value="<?php echo htmlspecialchars($formData['longitude'] ?? ''); ?>">
        </div>
        <div class="form-group full">
            <label>Website / Booking URL</label>
            <input type="url" name="hotel_details_link" value="<?php echo htmlspecialchars($formData['hotel_details_link'] ?? ''); ?>">
        </div>
    </div>
</div>

<div class="card">
    <div class="card-title">Ratings & Visibility</div>
    <div class="form-grid">
        <div class="form-group">
            <label>Star Rating</label>
            <input type="text" name="star_rating" value="<?php echo htmlspecialchars($formData['star_rating'] ?? ''); ?>" placeholder="e.g. 5 Star">
        </div>
        <div class="form-group">
            <label>Google Rating</label>
            <input type="number" step="0.1" min="0" max="5" name="google_rating" value="<?php echo htmlspecialchars($formData['google_rating'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Google Ratings Count</label>
            <input type="number" name="google_user_ratings_total" value="<?php echo htmlspecialchars($formData['google_user_ratings_total'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="highlighted" <?php echo !empty($formData['highlighted']) ? 'checked' : ''; ?>> Featured / Highlighted</label>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-title">Pricing & Stay Details</div>
    <div class="form-grid">
        <div class="form-group">
            <label>Base Price <span style="font-size:10px;color:var(--muted);font-weight:400;">(auto-synced from Room Rates → Total Amount/Night)</span></label>
            <input type="number" name="base_price" value="<?php echo htmlspecialchars($formData['base_price'] ?? ''); ?>" style="opacity:.7;">
        </div>
        <div class="form-group">
            <label>Taxes <span style="font-size:10px;color:var(--muted);font-weight:400;">(auto-synced from Room Rates → Total Tax/Night)</span></label>
            <input type="number" name="taxes" value="<?php echo htmlspecialchars($formData['taxes'] ?? ''); ?>" style="opacity:.7;">
        </div>
        <div class="form-group">
            <label>Discounted Price <span style="font-size:10px;color:var(--muted);font-weight:400;">(auto-synced from Room Rates)</span></label>
            <input type="number" name="discounted_price" value="<?php echo htmlspecialchars($formData['discounted_price'] ?? ''); ?>" style="opacity:.7;">
        </div>
        <div class="form-group">
            <label>Discount % <span style="font-size:10px;color:var(--muted);font-weight:400;">(auto-synced from Room Rates → Discount %)</span></label>
            <input type="number" name="discount_percentage" value="<?php echo htmlspecialchars($formData['discount_percentage'] ?? ''); ?>" style="opacity:.7;">
        </div>
        <div class="form-group">
            <label>Reward Points <span style="font-size:10px;color:var(--muted);font-weight:400;">(auto-synced from Room Rates)</span></label>
            <input type="number" name="reward_points" value="<?php echo htmlspecialchars($formData['reward_points'] ?? ''); ?>" style="opacity:.7;">
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="is_per_night" <?php echo !empty($formData['is_per_night']) ? 'checked' : ''; ?>> Price Is Per Night</label>
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="dnd_status" <?php echo !empty($formData['dnd_status']) ? 'checked' : ''; ?>> DND / Unavailable</label>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-title">Wedding Presentation</div>
    <div class="form-grid">
        <div class="form-group">
            <label>Price Per Plate</label>
            <input type="number" name="price_per_plate" value="<?php echo htmlspecialchars($formData['price_per_plate'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Package Cost</label>
            <input type="text" name="package_cost" value="<?php echo htmlspecialchars($formData['package_cost'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Min Guests</label>
            <input type="number" name="capacity_min" value="<?php echo htmlspecialchars($formData['capacity_min'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Max Guests</label>
            <input type="number" name="capacity_max" value="<?php echo htmlspecialchars($formData['capacity_max'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Rooms</label>
            <input type="number" name="rooms" value="<?php echo htmlspecialchars($formData['rooms'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Acres</label>
            <input type="number" step="0.1" name="acres" value="<?php echo htmlspecialchars($formData['acres'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Price Range</label>
            <select name="price_range_id">
                <option value="">- Select -</option>
                <?php foreach ($priceRanges as $priceRange): ?>
                <option value="<?php echo $priceRange['id']; ?>" <?php echo (string) ($formData['price_range_id'] ?? '') === (string) $priceRange['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($priceRange['label']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Capacity Category</label>
            <select name="capacity_category_id">
                <option value="">- Select -</option>
                <?php foreach ($capacities as $capacity): ?>
                <option value="<?php echo $capacity['id']; ?>" <?php echo (string) ($formData['capacity_category_id'] ?? '') === (string) $capacity['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($capacity['label']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group full">
            <label>Description</label>
            <textarea name="description" rows="5"><?php echo htmlspecialchars($formData['description'] ?? ''); ?></textarea>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-title">Venue Types</div>
    <div style="display:flex;flex-wrap:wrap;gap:12px;">
        <?php foreach ($venueTypes as $type): ?>
        <label class="checkbox-label">
            <input type="checkbox" name="venue_types[]" value="<?php echo $type['id']; ?>" <?php echo in_array((string) $type['id'], array_map('strval', $formData['venue_types'] ?? []), true) ? 'checked' : ''; ?>>
            <?php echo htmlspecialchars($type['label']); ?>
        </label>
        <?php endforeach; ?>
    </div>
</div>

<div class="card">
    <div class="card-title">Amenities</div>
    <?php $amenityOptions = adminAmenityOptions(); ?>
    <?php $selectedAmenityIds = array_map('intval', $formData['selected_amenity_ids'] ?? []); ?>
    <div class="form-grid">
        <div class="form-group full">
            <label>Choose amenities from backend list</label>
            <div style="border:1px solid var(--border); background:var(--bg); max-height:260px; overflow-y:auto; padding:10px; border-radius:6px;">
                <?php foreach ($amenityOptions as $amenity): ?>
                <label class="checkbox-label" style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                    <input type="checkbox" name="amenities[]" value="<?php echo (int) $amenity['id']; ?>" <?php echo in_array((int) $amenity['id'], $selectedAmenityIds, true) ? 'checked' : ''; ?>>
                    <?php echo htmlspecialchars($amenity['name']); ?>
                </label>
                <?php endforeach; ?>
            </div>
            <div class="form-hint">These are the same backend amenities used by frontend filters.</div>
        </div>
        <div class="form-group full">
            <label>Additional amenities (one per line)</label>
            <textarea name="custom_amenities" rows="4"><?php echo htmlspecialchars($formData['custom_amenities'] ?? ''); ?></textarea>
            <div class="form-hint">Use only for amenities not already included in the backend list.</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-title">Attractions</div>
    <?php $attractionOptions = adminAttractionOptions(); ?>
    <?php $selectedAttractionIds = array_map('intval', $formData['selected_attraction_ids'] ?? []); ?>
    <div class="form-grid">
        <div class="form-group full">
            <label>Choose attractions from backend list</label>
            <div style="border:1px solid var(--border); background:var(--bg); max-height:260px; overflow-y:auto; padding:10px; border-radius:6px;">
                <?php foreach ($attractionOptions as $attraction): ?>
                <label class="checkbox-label" style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                    <input type="checkbox" name="attractions[]" value="<?php echo (int) $attraction['id']; ?>" <?php echo in_array((int) $attraction['id'], $selectedAttractionIds, true) ? 'checked' : ''; ?>>
                    <?php echo htmlspecialchars($attraction['name'] ?? ''); ?>
                </label>
                <?php endforeach; ?>
            </div>
            <div class="form-hint">These attractions will be displayed on the frontend for this hotel.</div>
        </div>
    </div>
</div>

<style>
.rate-card { background: var(--surface2); border: 1px solid var(--border); border-radius: 6px; padding: 16px; margin-bottom: 12px; }
.rate-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
.rate-card-title { font-size: 13px; font-weight: 600; color: var(--accent); text-transform: uppercase; letter-spacing: 0.5px; }
.rate-card-remove { background: none; border: none; color: var(--muted); font-size: 18px; cursor: pointer; padding: 2px 6px; line-height: 1; border-radius: 3px; }
.rate-card-remove:hover { color: var(--danger); background: rgba(255,80,80,0.08); }
.rate-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px 14px; }
.rate-field { display: flex; flex-direction: column; gap: 4px; }
.rate-field label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--muted); }
.rate-field input, .rate-field select, .rate-field textarea { background: var(--bg); border: 1px solid var(--border); color: var(--text); padding: 6px 8px; border-radius: 3px; font-family: inherit; font-size: 13px; width: 100%; box-sizing: border-box; }
.rate-field textarea { resize: vertical; min-height: 60px; }
.rate-field-full { grid-column: 1 / -1; }
.rate-add-btn { background: var(--surface2); border: 1px dashed var(--border); color: var(--muted); padding: 10px 16px; cursor: pointer; border-radius: 4px; font-family: inherit; font-size: 13px; width: 100%; text-align: center; transition: all 0.15s; display: block; margin-top: 4px; }
.rate-add-btn:hover { border-color: var(--accent); color: var(--accent); }
</style>

<div class="card">
    <div class="card-title">Room Rates</div>
    <div id="rate-card-list"></div>
    <button type="button" class="rate-add-btn" id="rate-add-btn">+ Add Room Rate</button>
    <textarea name="room_rates_json" id="room-rates-hidden" style="display:none"><?php echo htmlspecialchars($formData['room_rates_json'] ?? '[]'); ?></textarea>
</div>

<script>
(function () {
    var hiddenTA = document.getElementById('room-rates-hidden');
    var list     = document.getElementById('rate-card-list');
    var addBtn   = document.getElementById('rate-add-btn');
    if (!hiddenTA || !list || !addBtn) return;

    var FIELDS = [
        { key: 'refundable',                        label: 'Refundable',                    type: 'select',  options: ['', 'NON_REFUNDABLE', 'REFUNDABLE', 'PARTIALLY_REFUNDABLE'] },
        { key: 'coupon_code',                       label: 'Coupon Code',                   type: 'text'  },
        { key: 'discount_percentage',               label: 'Discount %',                    type: 'number' },
        { key: 'total_amount_per_night',            label: 'Total Amount / Night (₹)',       type: 'number' },
        { key: 'total_tax_per_night',               label: 'Total Tax / Night (₹)',          type: 'number' },
        { key: 'total_discount_per_night',          label: 'Total Discount / Night (₹)',     type: 'number' },
        { key: 'discounted_price',                  label: 'Discounted Price (₹)',           type: 'number' },
        { key: 'discounted_price_per_night',        label: 'Discounted Price / Night (₹)',  type: 'number' },
        { key: 'pre_applied_offer_discount',        label: 'Pre-Applied Discount (₹)',       type: 'number' },
        { key: 'pre_applied_offer_discount_per_night', label: 'Pre-Applied Discount / Night (₹)', type: 'number' },
        { key: 'reward_points',                     label: 'Reward Points',                 type: 'number' },
        { key: 'coupon_description',                label: 'Coupon Description',             type: 'textarea', full: true },
        { key: 'coupon_description_per_night',      label: 'Coupon Description / Night',    type: 'textarea', full: true },
        // { key: 'inclusions',                        label: 'Inclusions (one per line)',      type: 'textarea', full: true },
    ];

    function syncHidden() {
        var cards = list.querySelectorAll('.rate-card');
        var rates = [];
        cards.forEach(function (card) {
            var rate = {};
            FIELDS.forEach(function (f) {
                var el = card.querySelector('[data-key="' + f.key + '"]');
                if (!el) return;
                var val = el.value.trim();
                if (f.key === 'inclusions') {
                    rate.inclusions = val === '' ? [] : val.split('\n').map(function (s) { return s.trim(); }).filter(Boolean);
                } else if (f.type === 'number') {
                    rate[f.key] = val === '' ? null : Number(val);
                } else {
                    rate[f.key] = val;
                }
            });
            rates.push(rate);
        });
        hiddenTA.value = JSON.stringify(rates);
    }

    function renumberCards() {
        var cards = list.querySelectorAll('.rate-card');
        cards.forEach(function (card, i) {
            var title = card.querySelector('.rate-card-title');
            if (title) title.textContent = 'Rate #' + (i + 1);
        });
    }

    function addRateCard(data) {
        data = data || {};
        var card = document.createElement('div');
        card.className = 'rate-card';

        var header = document.createElement('div');
        header.className = 'rate-card-header';
        var titleEl = document.createElement('span');
        titleEl.className = 'rate-card-title';
        titleEl.textContent = 'Rate #' + (list.querySelectorAll('.rate-card').length + 1);
        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'rate-card-remove';
        removeBtn.title = 'Remove rate';
        removeBtn.textContent = '\u00d7';
        removeBtn.addEventListener('click', function () {
            card.remove();
            renumberCards();
            syncHidden();
        });
        header.appendChild(titleEl);
        header.appendChild(removeBtn);
        card.appendChild(header);

        var grid = document.createElement('div');
        grid.className = 'rate-grid';

        FIELDS.forEach(function (f) {
            var wrap = document.createElement('div');
            wrap.className = 'rate-field' + (f.full ? ' rate-field-full' : '');
            var lbl = document.createElement('label');
            lbl.textContent = f.label;
            var el;
            if (f.type === 'select') {
                el = document.createElement('select');
                f.options.forEach(function (opt) {
                    var o = document.createElement('option');
                    o.value = opt;
                    o.textContent = opt === '' ? '— Select —' : opt;
                    el.appendChild(o);
                });
                var currentVal = (data[f.key] !== undefined && data[f.key] !== null) ? String(data[f.key]) : '';
                el.value = currentVal;
            } else if (f.type === 'textarea') {
                el = document.createElement('textarea');
                el.rows = 3;
                if (f.key === 'inclusions') {
                    var incl = data.inclusions || data.inclusions_json || [];
                    el.value = Array.isArray(incl) ? incl.join('\n') : String(incl || '');
                } else {
                    el.value = (data[f.key] !== undefined && data[f.key] !== null) ? String(data[f.key]) : '';
                }
            } else {
                el = document.createElement('input');
                el.type = f.type;
                el.value = (data[f.key] !== undefined && data[f.key] !== null) ? String(data[f.key]) : '';
            }
            el.setAttribute('data-key', f.key);
            el.addEventListener('input', syncHidden);
            el.addEventListener('change', syncHidden);
            wrap.appendChild(lbl);
            wrap.appendChild(el);
            grid.appendChild(wrap);
        });

        card.appendChild(grid);
        list.appendChild(card);
        syncHidden();
    }

    // Load existing rates from the hidden textarea
    try {
        var existing = JSON.parse(hiddenTA.value || '[]');
        if (Array.isArray(existing)) {
            existing.forEach(function (rate) { addRateCard(rate); });
        }
    } catch (e) { /* ignore parse errors */ }

    addBtn.addEventListener('click', function () { addRateCard({}); });
})();
</script>

<style>
.img-row { display:flex; align-items:center; gap:8px; background:var(--surface2); border:1px solid var(--border); border-radius:4px; padding:8px; }
.img-thumb { width:56px; height:56px; object-fit:cover; border-radius:3px; flex-shrink:0; background:var(--border); }
.img-url-input { flex:2; min-width:0; background:var(--bg); border:1px solid var(--border); color:var(--text); padding:6px 8px; border-radius:3px; font-family:inherit; font-size:12px; }
.img-caption-input { flex:1; min-width:0; background:var(--bg); border:1px solid var(--border); color:var(--text); padding:6px 8px; border-radius:3px; font-family:inherit; font-size:12px; }
.img-star-btn { background:none; border:none; color:var(--muted); font-size:22px; cursor:pointer; padding:4px 6px; line-height:1; flex-shrink:0; transition:color 0.15s, transform 0.1s; }
.img-star-btn:hover { color:var(--accent2); transform:scale(1.15); }
.img-star-btn.featured { color:var(--accent2); }
.img-remove-btn { background:none; border:none; color:var(--muted); font-size:18px; cursor:pointer; padding:4px 6px; line-height:1; flex-shrink:0; }
.img-remove-btn:hover { color:var(--danger); }
.img-add-btn { margin-top:10px; background:var(--surface2); border:1px dashed var(--border); color:var(--muted); padding:9px 16px; cursor:pointer; border-radius:4px; font-family:inherit; font-size:12px; width:100%; text-align:center; transition:all 0.15s; display:block; }
.img-add-btn:hover { border-color:var(--accent); color:var(--accent); }
.img-upload-btn { background:var(--surface2); border:1px solid var(--border); color:var(--muted); font-size:11px; cursor:pointer; padding:4px 7px; border-radius:3px; line-height:1.4; flex-shrink:0; font-family:inherit; }
.img-upload-btn:hover { border-color:var(--accent); color:var(--accent); }
</style>

<div class="card">
    <div class="card-title">Images</div>
    <div class="form-group" style="margin-bottom:16px">
        <label>Upload Images</label>
        <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp">
        <div class="form-hint">Uploaded images are appended on save; star them as featured on the next edit.</div>
    </div>
    <div id="img-entry-list" style="display:flex;flex-direction:column;gap:8px"></div>
    <button type="button" class="img-add-btn" onclick="imgEntryAdd('','',false)">+ Add Image URL</button>
    <textarea name="image_entries" id="img-entries-hidden" style="display:none"><?php echo htmlspecialchars($formData['image_entries'] ?? ''); ?></textarea>
</div>

<script>
function autoSlug(input) {
    const slug = document.getElementById('slug');
    if (!slug.dataset.manual) {
        slug.value = input.value.toLowerCase().replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const slug = document.getElementById('slug');
    if (slug) {
        slug.addEventListener('input', function () {
            this.dataset.manual = 'true';
        });
    }

    // Initialise image entry rows from the hidden textarea
    var hidden = document.getElementById('img-entries-hidden');
    if (hidden && hidden.value.trim()) {
        hidden.value.split('\n').forEach(function (line) {
            line = line.trim();
            if (!line) return;
            var featured = false;
            if (line.charAt(0) === '*') { featured = true; line = line.slice(1).trim(); }
            var parts = line.split('|').map(function (p) { return p.trim(); });
            imgEntryAdd(parts[0] || '', parts[1] || '', featured);
        });
    }
});

function imgEntrySync() {
    var rows = document.querySelectorAll('#img-entry-list .img-row');
    var lines = [];
    rows.forEach(function (row) {
        var url = row.querySelector('.img-url-input').value.trim();
        if (!url) return;
        var caption = row.querySelector('.img-caption-input').value.trim();
        var starred = row.querySelector('.img-star-btn').classList.contains('featured');
        var line = caption ? (url + ' | ' + caption) : url;
        lines.push(starred ? ('* ' + line) : line);
    });
    document.getElementById('img-entries-hidden').value = lines.join('\n');
}

function imgEntryAdd(url, caption, featured) {
    var list = document.getElementById('img-entry-list');

    // Un-feature any currently starred row when adding a new featured one
    if (featured) {
        list.querySelectorAll('.img-star-btn.featured').forEach(function (b) {
            b.classList.remove('featured');
            b.textContent = '\u2606';
        });
    }

    var row = document.createElement('div');
    row.className = 'img-row';
    row.innerHTML = '<img class="img-thumb" alt="">'
        + '<input type="text" class="img-url-input" placeholder="https://image-url.jpg">'
        + '<input type="text" class="img-caption-input" placeholder="Caption (optional)">'
        + '<button type="button" class="img-upload-btn" title="Upload from computer">&#128193; Upload</button>'
        + '<input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" style="display:none">'
        + '<button type="button" class="img-star-btn" title="Set as featured image">\u2606</button>'
        + '<button type="button" class="img-remove-btn" title="Remove">\u00d7</button>';

    var thumb      = row.querySelector('.img-thumb');
    var urlInput   = row.querySelector('.img-url-input');
    var uploadBtn  = row.querySelector('.img-upload-btn');
    var fileInput  = row.querySelector('input[type="file"]');
    var capInput  = row.querySelector('.img-caption-input');
    var starBtn   = row.querySelector('.img-star-btn');
    var removeBtn = row.querySelector('.img-remove-btn');

    // Set values via property to avoid HTML injection
    urlInput.value = url || '';
    capInput.value = caption || '';
    if (url) { thumb.src = url; }
    if (featured) { starBtn.classList.add('featured'); starBtn.textContent = '\u2605'; }

    urlInput.addEventListener('input', function () {
        if (!fileInput.files.length) {
            thumb.src = this.value || '';
        }
        imgEntrySync();
    });
    capInput.addEventListener('input', imgEntrySync);

    uploadBtn.addEventListener('click', function () {
        fileInput.click();
    });

    fileInput.addEventListener('change', function () {
        if (!this.files[0]) return;
        var objectUrl = URL.createObjectURL(this.files[0]);
        thumb.src = objectUrl;
        // Clear manual URL since this row is now a file upload
        urlInput.value = '';
        imgEntrySync();
    });

    starBtn.addEventListener('click', function () {
        // Remove featured from every row, then set only this one
        document.querySelectorAll('#img-entry-list .img-star-btn.featured').forEach(function (b) {
            b.classList.remove('featured');
            b.textContent = '\u2606';
        });
        this.classList.add('featured');
        this.textContent = '\u2605';
        imgEntrySync();
    });

    removeBtn.addEventListener('click', function () {
        row.remove();
        imgEntrySync();
    });

    list.appendChild(row);
    imgEntrySync();
}
</script>