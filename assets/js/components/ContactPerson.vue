<template>
  <div class="contact-component">
    <div class="contact-component-form">
      <div class="contact-component-form-header">
        <div class="contact-component-form-header-actions-new">
          <h3 v-if="contact.id">Eintrag bearbeiten</h3>
          <h3 v-else>Eintrag erstellen</h3>
          <a class="button" @click="showPreview = true"
            ><span class="material-icons">visibility</span></a
          >
          <a
            class="button warning"
            @click="contact.isPublic = true"
            v-if="!contact.isPublic"
            >Privat</a
          >
          <a
            class="button success"
            @click="contact.isPublic = false"
            v-if="contact.isPublic"
            >Öffentlich</a
          >
          <a @click="locale = 'de'" class="button" :class="{ primary: locale === 'de' }"
            >DE</a
          >
          <a @click="locale = 'fr'" class="button" :class="{ primary: locale === 'fr' }"
            >FR</a
          >
          <a @click="locale = 'it'" class="button" :class="{ primary: locale === 'it' }"
            >IT</a
          >
          <a class="button error" @click="clickDelete()" v-if="contact.id" title="Löschen"
            ><span class="material-icons">delete</span></a
          >
          <a class="button warning" @click="clickCancel()" title="Abbrechen">
            <span class="material-icons">close</span>
          </a>
          <a class="button primary" @click="clickSave()" title="Speichern">Speichern</a>
        </div>
        <div
          class="contact-component-form-header-actions-new diff-header"
          v-if="diff && !isDeleteRequest"
        >
          <h3>Externe Kontaktdaten</h3>
          <a
            v-if="!$route.fullPath.includes('/edit') && !isDeleteRequest"
            class="button error"
            @click="discardAll"
            >Änderungen verwerfen</a
          >
          <a
            v-if="!$route.fullPath.includes('/edit') && !isDeleteRequest"
            class="button primary"
            @click="mergeAll(locale)"
            >Alle Daten übernehmen</a
          >
        </div>
        <div v-else class="contact-component-form-header-actions-new diff-header"></div>
      </div>

      <div class="contact-component-form-section" v-if="formErrors.length">
        <ul class="errors">
          <li class="error" v-for="error in formErrors">
            {{ error.message }}
          </li>
        </ul>
      </div>

      <div style="padding-top: 0px" class="contact-component-form-section">
        <div class="contact-component-form-row">
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-2">
                <label for="gender">Anrede</label>
                <div class="select-wrapper">
                  <select class="form-control" v-model="contact.gender">
                    <option value="male">Herr</option>
                    <option value="female">Frau</option>
                    <option value="other">Keine Angabe</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <label for="academicTitle">Titel</label>
                <input
                  id="academicTitle"
                  type="text"
                  class="form-control"
                  v-model="contact.academicTitle"
                />
              </div>
            </div>
          </div>
          <div class="contact-component-form-section">
            <span class="error" v-if="isDeleteRequest"
              >Dieser Kontakt wurde gelöscht. Für Endgültige Löschung auf den Papierkorb
              klicken.
            </span>

            <div v-if="diff && !isDeleteRequest" class="row">
              <div class="col-md-2" :class="{ disabled: !isFieldChanged('gender') }">
                <label for="genderDiff">
                  <span
                    v-if="isFieldChanged('gender')"
                    class="material-icons"
                    @click="mergeField('gender')"
                    >keyboard_backspace</span
                  >
                  Anrede
                </label>
                <select
                  id="genderDiff"
                  class="form-control"
                  :value="isFieldChanged('gender') ? diff.gender : contact.gender || null"
                  readonly
                >
                  <option value="male">Herr</option>
                  <option value="female">Frau</option>
                  <option value="other">Keine Angabe</option>
                </select>
              </div>
              <div
                class="col-md-4"
                :class="{ disabled: !isFieldChanged('academicTitle') }"
              >
                <label for="academicTitleDiff">
                  <span
                    v-if="isFieldChanged('academicTitle')"
                    class="material-icons"
                    @click="mergeField('academicTitle')"
                    >keyboard_backspace</span
                  >Titel</label
                >
                <input
                  id="academicTitleDiff"
                  type="text"
                  class="form-control"
                  :value="
                    isFieldChanged('academicTitle')
                      ? diff.academicTitle
                      : contact.academicTitle || ''
                  "
                  :class="{ 'field-changed': isFieldChanged('academicTitle') }"
                />
              </div>
            </div>
          </div>
        </div>

        <div class="contact-component-form-row">
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-6">
                <label for="firstName">Vorname</label>
                <input
                  id="firstName"
                  type="text"
                  class="form-control"
                  v-model="contact.firstName"
                  :class="{ 'field-changed': isFieldChanged('firstName') }"
                />
              </div>
              <div class="col-md-6">
                <label for="lastName">Nachname</label>
                <input
                  id="lastName"
                  type="text"
                  class="form-control"
                  v-model="contact.lastName"
                  :class="{ 'field-changed': isFieldChanged('firstName') }"
                />
              </div>
            </div>
          </div>
          <div class="contact-component-form-section">
            <div v-if="diff && !isDeleteRequest" class="row">
              <div class="col-md-6" :class="{ disabled: !isFieldChanged('firstName') }">
                <label for="firstNameDiff">
                  <span
                    v-if="isFieldChanged('firstName')"
                    class="material-icons"
                    @click="mergeField('firstName')"
                    >keyboard_backspace</span
                  >
                  Vorname
                </label>
                <input
                  id="firstNameDiff"
                  type="text"
                  class="form-control"
                  :value="
                    isFieldChanged('firstName') ? diff.firstName : contact.firstName || ''
                  "
                  readonly
                />
              </div>
              <div class="col-md-6" :class="{ disabled: !isFieldChanged('lastName') }">
                <label for="lastName">
                  <span class="material-icons" @click="mergeField('lastName')"
                    >keyboard_backspace</span
                  >Nachname</label
                >
                <input
                  id="lastName"
                  type="text"
                  class="form-control"
                  :value="
                    isFieldChanged('lastName') ? diff.lastName : contact.lastName || ''
                  "
                  readonly
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Straße -->
        <div class="contact-component-form-row">
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-12">
                <label for="street">Strasse</label>
                <input
                  id="street"
                  type="text"
                  class="form-control"
                  v-model="contact.street"
                />
              </div>
            </div>
          </div>
          <div class="contact-component-form-section">
            <div v-if="diff && !isDeleteRequest" class="row">
              <div class="col-md-12" :class="{ disabled: !isFieldChanged('street') }">
                <label for="streetDiff">
                  <span
                    v-if="isFieldChanged('street')"
                    class="material-icons"
                    @click="mergeField('street')"
                    >keyboard_backspace</span
                  >
                  Strasse
                </label>
                <input
                  id="streetDiff"
                  type="text"
                  class="form-control"
                  :value="isFieldChanged('street') ? diff.street : contact.street || ''"
                  readonly
                />
              </div>
            </div>
          </div>
        </div>

        <!-- PLZ und Ort -->
        <div class="contact-component-form-row">
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-4">
                <label for="zipCode">PLZ</label>
                <input
                  id="zipCode"
                  type="text"
                  class="form-control"
                  v-model="contact.zipCode"
                />
              </div>
              <div class="col-md-8" v-if="locale === 'de'">
                <label for="city">Ort</label>
                <input
                  id="city"
                  type="text"
                  class="form-control"
                  v-model="contact.city"
                />
              </div>
              <div class="col-md-8" v-else>
                <label for="city">Ort (Übersetzung {{ locale.toUpperCase() }})</label>
                <input
                  id="city"
                  type="text"
                  class="form-control"
                  v-model="contact.translations[locale].city"
                />
              </div>
            </div>
          </div>
          <div class="contact-component-form-section">
            <div v-if="diff && !isDeleteRequest" class="row">
              <div class="col-md-4" :class="{ disabled: !isFieldChanged('zipCode') }">
                <label for="zipCodeDiff">
                  <span
                    v-if="isFieldChanged('zipCode')"
                    class="material-icons"
                    @click="mergeField('zipCode')"
                    >keyboard_backspace</span
                  >
                  PLZ
                </label>
                <input
                  id="zipCodeDiff"
                  type="text"
                  class="form-control"
                  :value="
                    isFieldChanged('zipCode') ? diff.zipCode : contact.zipCode || ''
                  "
                  readonly
                />
              </div>
              <!-- Diff Section for City -->
              <div
                class="col-md-8"
                :class="{ disabled: !isFieldChanged('city', locale) }"
              >
                <label for="cityDiff">
                  <span
                    v-if="isFieldChanged('city', locale)"
                    class="material-icons"
                    @click="mergeField('city', locale)"
                    >keyboard_backspace</span
                  >
                  Ort
                </label>
                <input
                  id="cityDiff"
                  type="text"
                  class="form-control"
                  :value="getDiffValue('city', locale)"
                  readonly
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Land und Kanton -->
        <div class="contact-component-form-row">
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-6">
                <label for="country">Land</label>
                <div class="select-wrapper">
                  <select
                    class="form-control"
                    @change="
                      contact.country =
                        getCountryById(parseInt($event.target.value)) || null;
                      contact.state = null;
                    "
                    :value="contact.country?.id"
                  >
                    <option :value="null"></option>
                    <option v-for="country in countries" :value="country.id">
                      {{ country.name }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="col-md-6" v-if="contact.country?.name === 'Schweiz'">
                <label for="province">Kanton</label>
                <div class="select-wrapper">
                  <select
                    class="form-control"
                    @change="
                      contact.state = getStateById(parseInt($event.target.value)) || null
                    "
                    :value="contact.state?.id"
                  >
                    <option :value="null"></option>
                    <option v-for="state in states" :value="state.id">
                      {{ state.name }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <div class="contact-component-form-section">
            <div v-if="diff && !isDeleteRequest" class="row">
              <div class="col-md-6" :class="{ disabled: !isFieldChanged('country') }">
                <label for="countryDiff">
                  <span
                    v-if="isFieldChanged('country')"
                    class="material-icons"
                    @click="mergeField('country')"
                    >keyboard_backspace</span
                  >
                  Land
                </label>
                <select
                  id="countryDiff"
                  class="form-control"
                  :value="
                    isFieldChanged('country') ? diff.country : contact.country?.id || null
                  "
                  readonly
                >
                  <option :value="null"></option>
                  <option v-for="country in countries" :value="country.id">
                    {{ country.name }}
                  </option>
                </select>
              </div>
              <div class="col-md-6" :class="{ disabled: !isFieldChanged('state') }">
                <label for="stateDiff">
                  <span
                    v-if="isFieldChanged('state')"
                    class="material-icons"
                    @click="mergeField('state')"
                    >keyboard_backspace</span
                  >
                  Kanton
                </label>
                <input
                  id="stateDiff"
                  type="text"
                  class="form-control"
                  :value="
                    isFieldChanged('state')
                      ? getStateById(diff.state)?.name || null
                      : contact.state?.name || null
                  "
                  readonly
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Sprache -->
        <div class="contact-component-form-row">
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-6">
                <label for="language">Sprache</label>
                <div class="select-wrapper">
                  <select
                    class="form-control"
                    @change="
                      contact.language =
                        getLanguageById(parseInt($event.target.value)) || null
                    "
                    :value="contact.language?.id"
                  >
                    <option :value="null"></option>
                    <option v-for="language in languages" :value="language.id">
                      {{ language.name }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="contact-component-form-section">
            <div v-if="diff && !isDeleteRequest" class="row">
              <div class="col-md-6" :class="{ disabled: !isFieldChanged('language') }">
                <label for="languageDiff">
                  <span
                    v-if="isFieldChanged('language')"
                    class="material-icons"
                    @click="mergeField('language')"
                    >keyboard_backspace</span
                  >
                  Sprache
                </label>
                <select
                  id="languageDiff"
                  class="form-control"
                  :value="
                    isFieldChanged('language')
                      ? diff.language
                      : contact.language?.id || null
                  "
                  :class="{ 'field-changed': isFieldChanged('language') }"
                  readonly
                >
                  <option :value="null"></option>
                  <option v-for="language in languages" :value="language.id">
                    {{ language.name }}
                  </option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- E-Mail und Telefon -->
        <div class="contact-component-form-row">
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-6">
                <label for="email">E-Mail</label>
                <input
                  id="email"
                  type="email"
                  class="form-control"
                  v-model="contact.email"
                />
              </div>
              <div class="col-md-6">
                <label for="phone">Telefon</label>
                <input
                  id="phone"
                  type="text"
                  class="form-control"
                  v-model="contact.phone"
                />
              </div>
            </div>
          </div>
          <div class="contact-component-form-section">
            <div v-if="diff && !isDeleteRequest" class="row">
              <div class="col-md-6" :class="{ disabled: !isFieldChanged('email') }">
                <label for="emailDiff">
                  <span
                    v-if="isFieldChanged('email')"
                    class="material-icons"
                    @click="mergeField('email')"
                    >keyboard_backspace</span
                  >
                  E-Mail
                </label>
                <input
                  id="emailDiff"
                  type="email"
                  class="form-control"
                  :value="isFieldChanged('email') ? diff.email : contact.email || ''"
                  readonly
                />
              </div>
              <div class="col-md-6" :class="{ disabled: !isFieldChanged('phone') }">
                <label for="phoneDiff">
                  <span
                    v-if="isFieldChanged('phone')"
                    class="material-icons"
                    @click="mergeField('phone')"
                    >keyboard_backspace</span
                  >
                  Telefon
                </label>
                <input
                  id="phoneDiff"
                  type="text"
                  class="form-control"
                  :value="isFieldChanged('phone') ? diff.phone : contact.phone || ''"
                  readonly
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Website -->
        <div class="contact-component-form-row">
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-6" v-if="locale === 'de'">
                <label for="website">Website</label>
                <input
                  id="website"
                  type="text"
                  class="form-control"
                  v-model="contact.website"
                />
              </div>
              <div class="col-md-6" v-else>
                <label for="website"
                  >Website (Übersetzung {{ locale.toUpperCase() }})</label
                >
                <input
                  id="website"
                  type="text"
                  class="form-control"
                  v-model="contact.translations[locale].website"
                />
              </div>
            </div>
          </div>
          <div class="contact-component-form-section">
            <div v-if="diff && !isDeleteRequest" class="row">
              <!-- Diff Section for Website -->
              <div
                class="col-md-6"
                :class="{ disabled: !isFieldChanged('website', locale) }"
              >
                <label for="websiteDiff">
                  <span
                    v-if="isFieldChanged('website', locale)"
                    class="material-icons"
                    @click="mergeField('website', locale)"
                    >keyboard_backspace</span
                  >
                  Website
                </label>
                <input
                  id="websiteDiff"
                  type="text"
                  class="form-control"
                  :value="
                    isFieldChanged('website', locale)
                      ? getDiffValue('website', locale)
                      : contact.website || ''
                  "
                  readonly
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Beschreibung -->
        <div class="contact-component-form-row">
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-12" v-if="locale === 'de'">
                <label for="description">Beschreibung</label>
                <ckeditor
                  id="description"
                  :editor="editor"
                  :config="editorConfig"
                  v-model="contact.description"
                >
                </ckeditor>
              </div>
              <div class="col-md-12" v-else>
                <label for="description"
                  >Beschreibung (Übersetzung {{ locale.toUpperCase() }})</label
                >
                <ckeditor
                  id="description"
                  :editor="editor"
                  :config="editorConfig"
                  v-model="contact.translations[locale].description"
                ></ckeditor>
              </div>
            </div>
          </div>
          <div class="contact-component-form-section">
            <div v-if="diff && !isDeleteRequest" class="row">
              <!-- Diff Section for Description -->
              <div
                class="col-md-12"
                :class="{ disabled: !isFieldChanged('description', locale) }"
              >
                <label for="descriptionDiff">
                  <span
                    v-if="isFieldChanged('description', locale)"
                    class="material-icons"
                    @click="mergeField('description', locale)"
                    >keyboard_backspace</span
                  >
                  Beschreibung
                </label>
                <ckeditor
                  id="descriptionDiff"
                  :editor="editor"
                  :config="editorConfig"
                  v-model="descriptionDiffValue"
                  readonly
                ></ckeditor>
              </div>
            </div>
          </div>
        </div>

        <div
          class="contact-component-form-section-group contact-group-component-form-section-group-transparent-bg"
        >
          <div class="row">
            <div class="col-md-6">
              <label for="topics">Kontakt Themen</label>
              <tag-selector
                v-if="contact && contact.topics !== undefined"
                id="topics"
                :model="contact.topics"
                :options="
                  topics.filter((topic) => !topic.context || topic.context === 'contact')
                "
                :searchType="'select'"
              ></tag-selector>
            </div>
            <div :class="{ disabled: !isFieldChanged('topics') }" class="col-md-6">
              <label for="topicsDiff">
                <span
                  v-if="isFieldChanged('topics', locale)"
                  class="material-icons"
                  @click="mergeField('topics', locale)"
                  >keyboard_backspace</span
                >Kontakt Themen</label
              >
              <tag-selector
                v-if="diff && diff.topics !== undefined"
                id="topicsDiff"
                :model="diff.topics"
                :options="
                  topics.filter((topic) => !topic.context || topic.context === 'contact')
                "
                :searchType="'select'"
                :readonly="true"
              ></tag-selector>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <label for="contactGroups">Kontaktgruppen</label>
              <tag-selector
                id="contactGroups"
                :model="contact.contactGroups"
                @change="addContactGroups($event)"
                :options="contactGroupOptions"
                :searchType="'select'"
                :isOptGroup="true"
              ></tag-selector>
            </div>
          </div>
        </div>

        <div class="contact-component-form-row">
          <div class="contact-component-form-section">
            <div class="col-md-12">
              <label>Anstellungen</label>
              <div
                class="contact-group-component-form-section-group contact-group-component-form-section-group-transparent-bg"
                v-for="(employment, index) in contact.employments"
                :key="employment.id"
              >
                <!-- Employment Editing Section -->
                <div class="row">
                  <!-- Left Column: Current Employment Fields -->
                  <div class="col-md-12">
                    <div class="row">
                      <div class="col-md-6">
                        <label for="company">Organisation</label>
                        <single-selector
                          id="company"
                          :value="employment.company"
                          @update="employment.company = $event || {}"
                          :options="contacts"
                        ></single-selector>
                      </div>

                      <!-- Role Field -->
                      <div class="col-md-3" v-if="locale === 'de'">
                        <label for="employment">Funktion</label>
                        <input
                          id="employment"
                          type="text"
                          class="form-control"
                          v-model="employment.role"
                          :class="{
                            'field-changed': isEmploymentFieldChanged(
                              'role',
                              employment.id
                            ),
                          }"
                          :placeholder="translate('role', contact)"
                        />
                      </div>
                      <div class="col-md-3" v-else>
                        <label for="employment"
                          >Funktion (Übersetzung {{ locale.toUpperCase() }})</label
                        >
                        <input
                          id="employment"
                          type="text"
                          class="form-control"
                          v-model="employment.translations[locale].role"
                          :class="{
                            'field-changed': isEmploymentFieldChanged(
                              'role',
                              employment.id,
                              locale
                            ),
                          }"
                          :placeholder="translate('role', contact)"
                        />
                      </div>

                      <!-- Official Address Checkbox -->
                      <div class="col-md-2">
                        <label>Hauptadresse</label>
                        <input
                          id="officialAddress"
                          :name="'official-address-' + index"
                          type="checkbox"
                          :checked="contact.officialEmployment === employment || false"
                          :true-value="employment"
                          v-model="contact.officialEmployment"
                        />
                      </div>

                      <!-- Remove Employment Button -->
                      <div class="col-md-1">
                        <label>&nbsp;</label>
                        <div
                          class="button error"
                          @click="clickRemoveEmployment(employment.id, index)"
                        >
                          <span title="Anstellung Entfernen" class="material-icons"
                            >delete</span
                          >
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Add Employment Button -->
              <div
                class="contact-group-component-form-section-group contact-group-component-form-section-group-transparent-bg"
              >
                <div class="button success" @click="clickAddEmployment()">
                  Anstellung hinzufügen
                </div>
              </div>
            </div>
          </div>
          <div class="contact-component-form-section">
            <div class="row" v-if="diff && !isDeleteRequest">
              <!-- Right Column: Diff Employment Fields -->
              <label
                :class="{
                  disabled: isEmploymentLabelHidden,
                }"
                >Anstellungen</label
              >

              <div
                v-for="(employment, index) in contact.employments"
                :key="'employmentDiff' + employment.id"
                class="col-md-12 diff-section contact-group-component-form-section-group contact-group-component-form-section-group-transparent-bg"
              >
                <div class="row">
                  <!-- Role Diff Field -->
                  <div
                    class="col-md-12"
                    :class="{
                      disabled:
                        !isEmploymentRemoved(employment.id) &&
                        (!employment?.id ||
                          !isEmploymentFieldChanged('role', employment.id, locale)),
                    }"
                  >
                    <label for="employmentDiff">
                      <span
                        v-if="
                          !isDeleted(employment.id) &&
                          (!employment?.id ||
                            isEmploymentFieldChanged('role', employment.id, locale))
                        "
                        class="material-icons"
                        @click="mergeField('role', locale, employment.id)"
                        >keyboard_backspace</span
                      >
                      {{ isEmploymentRemoved(employment.id) ? "Entfernt" : "Funktion" }}
                    </label>
                    <div style="display: flex" class="col-md-12">
                      <div style="display: flex; align-items: center" class="col-md-12">
                        <input
                          v-if="!isDeleted(employment.id)"
                          id="employmentDiff"
                          type="text"
                          class="form-control col-md-5"
                          :value="
                            employment?.id
                              ? getEmploymentDiffValue('role', employment.id, locale)
                              : ''
                          "
                          readonly
                        />
                        <p
                          style="margin: 0"
                          v-if="isDeleted(employment.id)"
                          class="error"
                        >
                          Dieser Kontakt arbeitet nicht mehr hier. Bitte löschen oder Alle
                          Übernehmen klicken und speichern.
                        </p>
                        <p v-else></p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      class="contact-component-overlay"
      v-if="showPreview"
      @click="showPreview = false"
    >
      <EmbedContactsView
        @click.stop
        @clickClose="showPreview = false"
        :contact="contact"
        :officialEmployment="contact.officialEmployment || null"
        :locale="locale"
      ></EmbedContactsView>
    </div>

    <transition name="fade">
      <Modal v-if="modal" :config="modal"></Modal>
    </transition>
  </div>
</template>

<script>
import { mapState, mapGetters } from "vuex";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import TagSelector from "./TagSelector";
import SingleSelector from "./SingleSelector";
import Modal from "./Modal";
import EmbedContactsView from "./EmbedContactsView.vue";

export default {
  data() {
    return {
      locale: "de",
      diff: null,
      contact: {
        type: "person",
        companyName: "",
        specification: "",
        gender: "male",
        academicTitle: "",
        firstName: "",
        lastName: "",
        street: "",
        zipCode: "",
        city: "",
        country: null,
        state: null,
        language: null,
        email: "",
        phone: "",
        website: "",
        description: "",
        officialEmployment: null,
        parent: null,
        children: [],
        employments: [],
        companies: [],
        employees: [],
        contactGroups: [],
        topics: [],
        translations: {
          fr: {},
          it: {},
        },
        isPublic: false,
      },
      originalContactGroups: [],
      newContactGroups: [],
      showPreview: false,
      modal: null,
      editor: ClassicEditor,
      editorConfig: {
        basicEntities: false,
        toolbar: {
          items: [
            "heading",
            "|",
            "bold",
            "italic",
            "link",
            "|",
            "numberedList",
            "bulletedList",
            "insertTable",
            "|",
            "undo",
            "redo",
          ],
        },
      },
      formErrors: [],
    };
  },
  components: {
    EmbedContactsView,
    TagSelector,
    SingleSelector,
    Modal,
  },
  computed: {
    ...mapState({
      contacts: (state) => state.contacts.filtered,
      contactGroups: (state) => state.contactGroups.all,
      countries: (state) => state.countries.all,
      states: (state) => state.states.all,
      languages: (state) => state.languages.all,
      topics: (state) => state.topics.all,
      selectedInboxItem: (state) => state.inbox.item,
    }),
    ...mapGetters({
      getContactById: "contacts/getById",
      getCountryById: "countries/getById",
      getStateById: "states/getById",
      getLanguageById: "languages/getById",
      getEmploymentById: "employments/getById",
      getTopicById: "topics/getById",
    }),
    contactGroupOptions() {
      let contactGroupOptions = [];

      let parentGroups = this.contactGroups.filter((group) => !group.parent);

      for (let parentGroup of parentGroups) {
        parentGroup.children = this.contactGroups.filter(
          (group) => group.parent?.id === parentGroup.id
        );

        if (parentGroup.children.length) {
          contactGroupOptions.push(parentGroup);
        }
      }

      return contactGroupOptions;
    },
    descriptionDiffValue: {
      get() {
        if (this.isFieldChanged("description", this.locale)) {
          return this.getDiffValue("description", this.locale);
        } else {
          return this.locale !== "de"
            ? this.contact.translations[this.locale]?.description || ""
            : this.contact.description || "";
        }
      },
    },
    isDeleteRequest() {
      return this.selectedInboxItem?.data?.delete ?? false;
    },
    isEmploymentLabelHidden() {
      () => {
        if (this.selectedInboxItem?.data?.removeEmploymentIds?.length) {
          return false;
        }

        return this.contact.employments.some((employment) =>
          this.isEmploymentFieldChanged("role", employment.id, this.locale)
        );
      };
    },
  },
  methods: {
    clickDelete() {
      this.modal = {
        title: "Eintrag löschen",
        description:
          "Sind Sie sicher dass Sie diesen Eintrag unwiderruflich löschen möchten?",
        actions: [
          {
            label: "Endgültig löschen",
            class: "error",
            onClick: () => {
              this.$store.dispatch("contacts/delete", this.contact.id).then(() => {
                this.$router.push("/contacts/person");
              });
              if (this.selectedInboxItem && this.selectedInboxItem.id) {
                this.$store.dispatch("inbox/delete", this.selectedInboxItem.id);
              }
            },
          },
          {
            label: "Abbrechen",
            class: "warning",
            onClick: () => {
              this.modal = null;
            },
          },
        ],
      };
    },
    clickCancel() {
      this.$router.push("/contacts/person");
    },
    async clickSave() {
      let contact = { ...this.contact };

      // this somehow fixes the bug that translations wont be saved despite being set
      this.contact.translations = {
        fr: {
          ...this.contact.translations.fr,
        },
        it: {
          ...this.contact.translations.it,
        },
      };

      if (this.contact.id) {
        contact = await this.$store.dispatch("contacts/update", this.contact);
      } else {
        contact = await this.$store.dispatch("contacts/create", this.contact);
      }

      if (contact.officialEmployment) {
        let officialEmployment = { ...this.contact.officialEmployment };
        officialEmployment.employee = contact;

        for (let contactGroup of contact.contactGroups) {
          if (this.newContactGroups.find((group) => group.id === contactGroup.id)) {
            let group = this.contact.contactGroups.find(
              (group) => group.id === contactGroup.id
            );

            group.employments.push(officialEmployment);
            group.contacts.push(contact);

            this.$store.dispatch("contactGroups/update", group);
          }
        }
      }

      if (this.selectedInboxItem && this.selectedInboxItem.id) {
        await this.$store.dispatch("inbox/delete", this.selectedInboxItem.id);
        // Redirect to the inbox or any desired route after deletion
        this.$router.push("/inbox");
      } else {
        // Redirect to the contacts list if no inbox item was processed
        this.$router.push("/contacts/person");
      }
    },
    clickAddEmployment() {
      let employment = {
        position: null,
        company: null,
        employee: null,
        role: null,
        contactGroups: [],
        translations: {
          fr: {
            role: null,
          },
          it: {
            role: null,
          },
        },
      };

      this.contact.employments.push(employment);
    },
    clickRemoveEmployment(employmentId, index) {
      let employment = this.contact.employments.splice(index, 1)[0];

      if (this.contact.officialEmployment?.id === employmentId) {
        this.contact.officialEmployment = null;
      }
    },
    clickSetOfficialEmployment(event) {
      if (event.target.checked) {
        this.contact.officialEmployment = this.getEmploymentById(event.target.value);
      } else {
        this.contact.officialEmployment = null;
      }
    },
    async reload() {
      if (this.$route.params.id) {
        this.$store.commit("contacts/set", {});
        this.$store.commit("inbox/set", {});

        let contactsData = await this.$store.dispatch("contactsData/loadFiltered", {
          contactType: "employee",
          params: { ids: [this.$route.params.id] },
        });

        this.contact = { ...contactsData.contacts[0] };

        this.originalContactGroups = [...this.contact.contactGroups];

        this.contact.employments = contactsData.employments;

        this.contact.officialEmployment =
          this.contact.employments.find(
            (employment) => employment.id === this.contact.officialEmployment?.id
          ) || null;

        const inboxId = this.$route.params.inbox_id;

        if (inboxId) {
          // Load the inbox item using the store
          await this.$store.dispatch("inbox/load", inboxId);
          this.diff = this.selectedInboxItem.data.changes;
        }
      }
    },
    translate(property, context) {
      if (this.locale === "de") {
        return (
          context[property] ||
          context.translations.fr[property] ||
          context.translations.it[property]
        );
      }
      if (this.locale === "fr") {
        return (
          context.translations.fr[property] ||
          context[property] ||
          context.translations.it[property]
        );
      }
      if (this.locale === "it") {
        return (
          context.translations.it[property] ||
          context.translations.fr[property] ||
          context[property]
        );
      }
      return context[property];
    },
    addContactGroups(groups) {
      this.newContactGroups = groups.filter(
        (group) =>
          !this.originalContactGroups
            .map((originalGroup) => originalGroup.id)
            .includes(group.id)
      );
    },
    getDiffValue(field, locale = null) {
      if (!this.diff) return "";

      const multilingualFields = ["website", "description", "city"];

      let diffValue, contactValue;

      if (multilingualFields.includes(field)) {
        if (locale && locale !== "de") {
          diffValue = this.diff.translations?.[locale]?.[field];
          contactValue = this.contact.translations?.[locale]?.[field];
        } else {
          diffValue = this.diff.translations?.de?.[field] || this.diff[field];
          contactValue = this.contact[field];
        }
      } else {
        if (locale && locale !== "de") {
          diffValue = this.diff.translations?.[locale]?.[field];
          contactValue = this.contact.translations?.[locale]?.[field];
        } else {
          diffValue = this.diff[field];
          contactValue = this.contact[field];
        }
      }

      // Only return diffValue if it differs from contactValue
      if (diffValue !== undefined && diffValue !== contactValue) {
        return diffValue;
      } else {
        return contactValue;
      }
    },
    isEmploymentFieldChanged(field, employmentId, locale = "de") {
      const employmentDiff = this.diff?.employments?.find((e) => e.id === employmentId);
      if (!employmentDiff) return false;

      if (locale === "de") {
        return (
          employmentDiff[field] !== undefined &&
          employmentDiff[field] !==
            this.contact.employments.find((e) => e.id === employmentId)[field]
        );
      } else {
        return (
          employmentDiff.translations?.[locale]?.[field] !== undefined &&
          employmentDiff.translations[locale][field] !==
            this.contact.employments.find((e) => e.id === employmentId)?.translations?.[
              locale
            ]?.[field]
        );
      }
    },
    getEmploymentDiffValue(field, employmentId, locale = "de") {
      const employmentDiff = this.diff?.employments?.find((e) => e.id === employmentId);
      if (!employmentDiff) return "";

      if (locale === "de") {
        return employmentDiff[field] !== undefined
          ? employmentDiff[field]
          : this.contact.employments.find((e) => e.id === employmentId)[field];
      } else {
        return employmentDiff.translations?.[locale]?.[field] !== undefined
          ? employmentDiff.translations[locale][field]
          : this.contact.employments.find((e) => e.id === employmentId)?.translations?.[
              locale
            ]?.[field];
      }
    },
    isEmploymentRemoved(employmentId) {
      // i did it like this because the employmentId is a string and it would take to long to refactor it
      let parsedId = parseInt(employmentId);
      let isRemoved = false;

      this.selectedInboxItem?.data?.removeEmploymentIds.forEach((element) => {
        if (parseInt(element) === parsedId) {
          isRemoved = true;
        }
      });

      return isRemoved;
    },
    isFieldChanged(field, locale = null) {
      if (!this.diff) return false;

      if (field === "topics") {
        // Compare topics array by checking IDs
        const diffTopicIds = (this.diff.topics || []).map((topic) => topic.id);
        const contactTopicIds = (this.contact.topics || []).map((topic) => topic.id);
        return JSON.stringify(diffTopicIds) !== JSON.stringify(contactTopicIds);
      }

      const multilingualFields = ["website", "description", "city"];
      let diffValue, contactValue;

      if (multilingualFields.includes(field)) {
        if (locale && locale !== "de") {
          diffValue = this.diff.translations?.[locale]?.[field];
          contactValue = this.contact.translations?.[locale]?.[field];
        } else {
          diffValue = this.diff.translations?.de?.[field] || this.diff[field];
          contactValue = this.contact[field];
        }
      } else {
        if (locale && locale !== "de") {
          diffValue = this.diff.translations?.[locale]?.[field];
          contactValue = this.contact.translations?.[locale]?.[field];
        } else {
          diffValue = this.diff[field];
          contactValue = this.contact[field];
        }
      }

      if (field === "state") {
        diffValue = this.diff.state;
        contactValue = this.contact.state?.id;
      }

      return diffValue !== undefined && diffValue !== contactValue;
    },

    mergeField(field, locale = null, employmentId = null) {
      if (employmentId) {
        const employment = this.contact.employments.find((e) => e.id === employmentId);
        if (!employment) return;

        if (locale && locale !== "de") {
          if (!employment.translations[locale]) {
            employment.translations[locale] = {};
          }
          employment.translations[locale][field] = this.diff.employments.find(
            (e) => e.id === employmentId
          )?.translations[locale]?.[field];
        } else {
          employment[field] = this.diff.employments.find((e) => e.id === employmentId)?.[
            field
          ];
        }
      } else {
        const multilingualFields = ["website", "description", "city"];

        if (multilingualFields.includes(field)) {
          if (locale && locale !== "de") {
            if (!this.contact.translations[locale]) {
              this.contact.translations[locale] = {};
            }
            if (this.diff.translations && this.diff.translations[locale]) {
              this.contact.translations[locale][field] = this.diff.translations[locale][
                field
              ];
            }
          } else {
            this.contact[field] = this.diff[field];
          }
        } else {
          if (locale && locale !== "de") {
            if (!this.contact.translations[locale]) {
              this.contact.translations[locale] = {};
            }
            if (this.diff.translations && this.diff.translations[locale]) {
              this.contact.translations[locale][field] = this.diff.translations[locale][
                field
              ];
            }
          } else {
            this.contact[field] = this.diff[field];
          }
        }
      }
      if (field === "language") {
        this.contact.language = this.getLanguageById(this.diff.language) || null;
      }

      if (field === "country") {
        this.contact.country = this.getCountryById(this.diff.country) || null;
      }

      if (field === "state") {
        this.contact.state = this.getStateById(this.diff.state) || null;
      }

      if (field === "topics") {
        this.contact.topics = [...this.diff.topics];
        return;
      }
    },
    mergeAll() {
      const fields = [
        "academicTitle",
        "city",
        "country",
        "state",
        "language",
        "website",
        "description",
        "gender",
        "firstName",
        "lastName",
        "street",
        "zipCode",
        "phone",
        "email",
        "topics",
        "state",
      ];

      const locales = ["de", "fr", "it"];

      fields.forEach((field) => {
        if (field === "topics") {
          // Handle topics specifically as an array of objects
          if (this.isFieldChanged("topics")) {
            this.mergeField("topics");
          }
        } else {
          // For other fields, handle multilingual fields
          locales.forEach((locale) => {
            if (this.isFieldChanged(field, locale === "de" ? null : locale)) {
              this.mergeField(field, locale === "de" ? null : locale);
            }
          });
        }
      });

      this.contact.employments = this.contact.employments.filter(
        (employment) => this.isDeleted(employment.id) === false
      );
    },
    async discardAll() {
      if (this.selectedInboxItem && this.selectedInboxItem.id) {
        await this.$store.dispatch("inbox/delete", this.selectedInboxItem.id);
        this.$router.push("/inbox");
      } else {
        this.$router.push("/contacts/person");
      }
    },
    isDeleted(id) {
      let deleted = false;
      this.selectedInboxItem?.data?.removeEmploymentIds.forEach((element) => {
        if (parseInt(element) === id) {
          deleted = true;
        }
      });
      return deleted;
    },
  },
  created() {
    this.reload();
    this.$store.dispatch("contacts/loadFiltered", { type: ["company"] });
  },
};
</script>
