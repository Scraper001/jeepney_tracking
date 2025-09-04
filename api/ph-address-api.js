// Simple Philippine address API integration
document.addEventListener("DOMContentLoaded", function () {
  // Function to fetch regions
  async function fetchRegions() {
    const regionSelect = document.getElementById("region");
    regionSelect.innerHTML =
      '<option value="" class = "text-black" style = "color:black;">Select Region</option>';

    try {
      const response = await fetch("https://psgc.gitlab.io/api/regions/");
      const regions = await response.json();

      regions
        .sort((a, b) => a.name.localeCompare(b.name))
        .forEach((region) => {
          const option = document.createElement("option");
          option.value = region.code;
          option.textContent = region.name;
          regionSelect.appendChild(option);
        });
    } catch (error) {
      console.error("Error fetching regions:", error);
    }
  }

  // Function to fetch provinces by region code
  async function fetchProvinces(regionCode) {
    const municipalitySelect = document.getElementById("municipality");
    municipalitySelect.innerHTML =
      '<option value="" class = "text-black" style = "color:black;">Select Province</option>';

    try {
      const response = await fetch(
        `https://psgc.gitlab.io/api/regions/${regionCode}/provinces/`
      );
      const provinces = await response.json();

      provinces
        .sort((a, b) => a.name.localeCompare(b.name))
        .forEach((province) => {
          const option = document.createElement("option");
          option.value = province.code;
          option.textContent = province.name;
          municipalitySelect.appendChild(option);
        });

      // Enable municipality select
      municipalitySelect.disabled = false;

      // Reset barangay select
      const barangaySelect = document.getElementById("barangay");
      barangaySelect.innerHTML =
        '<option value="" class="text-black" style="color:black;">Select Municipality</option>';
      barangaySelect.disabled = true;
    } catch (error) {
      console.error("Error fetching provinces:", error);
    }
  }

  // Function to fetch municipalities by province code
  async function fetchMunicipalities(provinceCode) {
    const barangaySelect = document.getElementById("barangay");
    barangaySelect.innerHTML =
      '<option value="" class = "text-black" style = "color:black;">Select Municipality</option>';

    try {
      const response = await fetch(
        `https://psgc.gitlab.io/api/provinces/${provinceCode}/municipalities/`
      );
      const municipalities = await response.json();

      municipalities
        .sort((a, b) => a.name.localeCompare(b.name))
        .forEach((municipality) => {
          const option = document.createElement("option");
          option.value = municipality.code;
          option.textContent = municipality.name;
          barangaySelect.appendChild(option);
        });

      // Enable barangay select
      barangaySelect.disabled = false;
    } catch (error) {
      console.error("Error fetching municipalities:", error);
    }
  }

  // Function to fetch barangays by municipality code
  async function fetchBarangays(municipalityCode) {
    // Create barangay container if it doesn't exist
    let barangayListContainer = document.getElementById(
      "barangay-list-container"
    );

    if (!barangayListContainer) {
      barangayListContainer = document.createElement("div");
      barangayListContainer.id = "barangay-list-container";
      barangayListContainer.className = "grid gap-2";

      const label = document.createElement("label");
      label.htmlFor = "barangay-select";
      label.className = "text-[13px] font-semibold flex items-center";
      label.innerHTML =
        'Barangay <span id="barangay-select-loading" class="ml-2 hidden"></span>';

      const select = document.createElement("select");
      select.id = "barangay-select";
      select.name = "barangay_code";
      select.required = true;
      select.className =
        "w-full rounded-xl border border-slate-200 bg-white/60 px-4 py-3 text-[15px] text-slate-900 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-200/60 dark:border-slate-800 dark:bg-transparent dark:text-slate-100 dark:focus:border-sky-500 dark:focus:ring-sky-400/20";
      select.innerHTML =
        '<option value="" class="text-black" style="color:black;">Select Barangay</option>';

      const errorMsg = document.createElement("p");
      errorMsg.id = "barangay-select-error";
      errorMsg.className = "hidden text-xs text-red-500";
      errorMsg.textContent = "Please select a barangay.";

      barangayListContainer.appendChild(label);
      barangayListContainer.appendChild(select);
      barangayListContainer.appendChild(errorMsg);

      // Add after f-barangay
      const barangayContainer = document.getElementById("f-barangay");
      barangayContainer.parentNode.insertBefore(
        barangayListContainer,
        barangayContainer.nextSibling
      );
    }

    const barangaySelect = document.getElementById("barangay-select");
    barangaySelect.innerHTML =
      '<option value="" class="text-black" style="color:black;">Select Barangay</option>';

    try {
      const response = await fetch(
        `https://psgc.gitlab.io/api/municipalities/${municipalityCode}/barangays/`
      );

      if (!response.ok) {
        throw new Error(`Failed to fetch barangays: ${response.status}`);
      }

      const barangays = await response.json();

      if (barangays && barangays.length > 0) {
        barangays
          .sort((a, b) => a.name.localeCompare(b.name))
          .forEach((barangay) => {
            const option = document.createElement("option");
            option.value = barangay.code;
            option.textContent = barangay.name;
            barangaySelect.appendChild(option);
          });

        barangayListContainer.classList.remove("hidden");
        barangaySelect.disabled = false;
      }
    } catch (error) {
      console.error("Error fetching barangays:", error);
    }
  }

  // Initialize the dropdowns
  fetchRegions();

  // Add event listeners
  const regionSelect = document.getElementById("region");
  regionSelect.addEventListener("change", function () {
    fetchProvinces(this.value);
    updateAddressPreview();
  });

  const municipalitySelect = document.getElementById("municipality");
  municipalitySelect.addEventListener("change", function () {
    fetchMunicipalities(this.value);
    updateAddressPreview();
  });

  const barangaySelect = document.getElementById("barangay");
  barangaySelect.addEventListener("change", function () {
    fetchBarangays(this.value);
    updateAddressPreview();
  });
});
