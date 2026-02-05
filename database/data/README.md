## Location datasets (`database/data`)

This folder is the **data layer** for the `laravel-location` package.
It is designed for two goals:

- Provide a **single master list of countries** (`countries.json`)
- Allow the community (or each application) to add **country-specific subdivisions** (provinces / cities / districts) under per-country folders

Everything in this README is written in **English** so it can be used by any project.

---

## Quick start (recommended workflow)

1) Generate or update `countries.json` (automatic)

```bash
php artisan location:generate-countries --pretty
```

2) (Optional) Add flag SVG assets

This package does not ship with a flag-downloader script. You have two options:

- Manually place files under `packages/laravel-location/assets/flags/` (recommended naming: `{key}.svg`)
- Use your own script/tooling to download flags from the URLs in `countries.json` (see section 3)

3) Add subdivisions for the countries you care about (e.g. `us/data.json`) (manual)

4) Import:

```bash
php artisan location:import
php artisan location:import us --force
```

---

## 1) Folder structure

Recommended structure:

- **`database/data/countries.json`** (required)
- **`database/data/{country_key}/data.json`** (optional, preferred; subdivisions)
- `database/data/{country_key}/subdivisions.json` (optional, legacy; still supported)

Example:

```
database/data/
  countries.json
  ir/
    data.json
  us/
    data.json
```

Rules:

- **`{country_key}` must match `countries.json[*].key`** (recommended: ISO2 lowercase like `ir`, `us`, `de`)
- Subdivision files are **optional**. If they do not exist, `location:import {key}` will skip provinces/cities/districts for that country.

---

## 2) How `countries.json` is produced

### 2.1 Generate countries dataset

The package ships with an Artisan command that generates `countries.json` from upstream datasets:

```bash
php artisan location:generate-countries --pretty
```

What it does (high-level):

- Downloads a countries dataset (default: RestCountries `countriesV3.1.json`)
- Downloads phone metadata (default: Google libphonenumber `PhoneNumberMetadata.xml`)
- Computes:
  - `key` (lowercase ISO2)
  - `mobile_prefix`
  - `validation` (mobile regex patterns)
  - `address_on_letter` template
- Enriches the output with rich metadata like `cca2`, `cca3`, timezones, etc.
- Writes the result to `database/data/countries.json` (or a custom path)

Useful options:

```bash
# custom output file
php artisan location:generate-countries --out=/absolute/path/countries.json --pretty

# custom upstream sources
php artisan location:generate-countries \
  --source=https://example.com/countries.json \
  --phone-metadata=https://example.com/PhoneNumberMetadata.xml \
  --pretty
```

### 2.2 What `location:import` reads from `countries.json`

`countries.json` may contain many fields, but `location:import` only persists these into the database:

- `key` (required in JSON, not stored; used to locate `database/data/{key}/...`)
- `name` (required; used as the unique identifier for import)
- `flag` (optional; filename like `us.svg`)
- `mobile_prefix` (optional)
- `validation` (optional; array of regex strings)
- `address_on_letter` (optional; string template)
- `status` (optional; boolean)

Everything else in `countries.json` is allowed (and useful), but it is not stored by default import.

Minimum example:

```json
[
  { "key": "us", "name": "United States" }
]
```

---

## 3) Flags and `assets/flags` (manual)

If `countries.json` contains a `flag` value (e.g. `"flag": "us.svg"`), it is strongly recommended to keep the SVG file in:

```
packages/laravel-location/assets/flags/us.svg
```

### 3.1 Where do flag files come from?

The `laravel-location` package **does not** download flags for you.
Flag assets have licensing and branding implications, so your application should decide where to source them.

Common approaches:

- **Use your existing icon set** (preferred in many products)
- Download from an external provider (e.g. `flag_svg_url` values inside `countries.json`)
- Use another open dataset (ensure you comply with license terms)

### 3.2 Recommended naming convention

Keep the filename aligned with `countries.json[*].key`:

- `key`: `us` → `flag`: `us.svg` → file: `assets/flags/us.svg`

This keeps `countries.flag` consistent with your assets.

### 3.3 Example: downloading flags using your own tooling

If your `countries.json` includes `flag_svg_url`, you can use a simple one-liner script.

#### Bash (Linux/macOS)

```bash
mkdir -p packages/laravel-location/assets/flags
jq -r '.[] | "\(.flag_svg_url) \(.flag)"' packages/laravel-location/database/data/countries.json \
  | while read -r url file; do
      [ -z "$url" ] && continue
      curl -fsSL "$url" -o "packages/laravel-location/assets/flags/$file"
    done
```

#### PowerShell (Windows)

```powershell
New-Item -ItemType Directory -Force -Path "packages/laravel-location/assets/flags" | Out-Null
$countries = Get-Content "packages/laravel-location/database/data/countries.json" -Raw | ConvertFrom-Json
foreach ($c in $countries) {
  if (-not $c.flag_svg_url) { continue }
  $out = Join-Path "packages/laravel-location/assets/flags" $c.flag
  Invoke-WebRequest -Uri $c.flag_svg_url -OutFile $out -UseBasicParsing
}
```

Tip: After downloading, spot-check a few SVG files for validity and consistency.

---

## 4) Subdivisions (`data.json`) – provinces, cities, districts

### 4.1 Which file name is used?

For a given country key, `location:import {key}` searches for:

1. `database/data/{key}/data.json` (**preferred**)
2. `database/data/{key}/subdivisions.json` (legacy fallback)

### 4.2 File format

The file can be either:

- An object with `provinces`:

```json
{
  "provinces": [
    {
      "name": "California",
      "cities": [
        {
          "name": "Los Angeles",
          "districts": [
            { "name": "Hollywood" }
          ]
        }
      ]
    }
  ]
}
```

Or a plain array:

```json
[
  { "name": "California", "cities": [] }
]
```

### 4.3 Supported fields and mapping rules

The importer uses only specific keys. Extra keys are allowed and ignored unless listed below.

#### Province

- `name` (required)
- `status` (optional; defaults to `true`)
- `cities` (optional; array)

#### City

- `name` (required)
- `status` (optional; defaults to `true`)
- `districts` (optional; array)

#### District

- `name` (required)
- `status` (optional; defaults to `true`)
- `subtitle` (optional; stored as `districts.subtitle`)
- `keywords` (optional; stored as `districts.keywords`)
- `search_keywords` (optional; legacy alias; stored as `districts.keywords`)

Keyword normalization:

- If the dataset provides `keywords` / `search_keywords` as a **string**, it will be split by `,` / `،` / `;` / `؛`
- The database always stores `keywords` as a **JSON array of strings**

---

## 5) Example: adding United States (US) subdivisions

### Step A) Ensure US exists in `countries.json`

```json
{
  "key": "us",
  "name": "United States",
  "flag": "us.svg",
  "mobile_prefix": 1,
  "status": true
}
```

### Step B) Create `database/data/us/data.json`

Create:

```
database/data/us/data.json
```

Example content (small sample):

```json
{
  "provinces": [
    {
      "name": "California",
      "cities": [
        {
          "name": "Los Angeles",
          "districts": [
            { "name": "Hollywood", "subtitle": "LA", "search_keywords": "Hollywood, Los Angeles" },
            { "name": "Downtown", "keywords": ["DTLA", "Los Angeles"] }
          ]
        },
        {
          "name": "San Francisco",
          "districts": [
            { "name": "Mission District", "keywords": ["Mission", "SF"] }
          ]
        }
      ]
    },
    {
      "name": "New York",
      "cities": [
        {
          "name": "New York City",
          "districts": [
            { "name": "Manhattan", "keywords": ["NYC", "New York"] },
            { "name": "Brooklyn", "keywords": ["NYC", "New York"] }
          ]
        }
      ]
    }
  ]
}
```

Tip: if you do not have districts yet, you can keep `"districts": []` (or omit it).

---

## 6) How do I create `data.json` for a country?

Subdivisions (provinces/cities/districts) are **not generated** by this package because there is no single global source with consistent licensing, completeness, and naming conventions.

Typical ways to build `database/data/{key}/data.json`:

1) **Start small and grow**
   - Add only provinces/states first
   - Add major cities next
   - Add districts/neighborhoods later (or keep `districts: []` until you have data)

2) **Convert an upstream dataset**
   - Export administrative divisions from an official source or a trusted dataset
   - Normalize names to your product’s needs
   - Convert to the JSON shape described in section 4

3) **Manual curation**
   - For high-value countries (where your users are), curate better names and keywords

Best practices:

- Keep JSON **stable** (avoid renaming items unless needed)
- Prefer `keywords` as an **array** in your dataset:

```json
{ "name": "Hollywood", "keywords": ["Hollywood", "Los Angeles", "LA"] }
```

- If you only have a string, `search_keywords` is acceptable:

```json
{ "name": "Hollywood", "search_keywords": "Hollywood, Los Angeles, LA" }
```

The importer will split it into an array.

---

## 7) Importing data with `location:import`

### Import all countries (countries table only)

```bash
php artisan location:import
```

### Import subdivisions for one or more countries

```bash
php artisan location:import us
php artisan location:import us ir
```

### Force update existing rows

Without `--force`, the importer only creates missing rows (or restores soft-deleted ones).  
With `--force`, it updates existing rows too.

```bash
php artisan location:import us --force
```

### Use a custom data directory

The custom directory must contain `countries.json`.

```bash
php artisan location:import us --data-path=/absolute/path/to/data
```

---

## 8) Data modeling notes and pitfalls

- **Country key mismatch**: if `countries.json` uses `"key": "us"`, your folder must be `database/data/us/`.
- **Countries are matched by name**: the importer finds existing countries by `name` (not by `key`). Keep names stable once imported.
- **Invalid JSON**: the command fails fast if it cannot decode JSON.
- **Matching by name**: provinces/cities/districts are matched by name under their parent:
  - Province: `(country_id, name)`
  - City: `(province_id, name)`
  - District: `(city_id, name)`
- **Large datasets**: keep files reasonably formatted and prefer `--pretty` only for human-edited files.

