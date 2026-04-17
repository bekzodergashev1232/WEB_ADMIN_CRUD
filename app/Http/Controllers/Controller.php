<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //
}
/**
 * public function getExcelUmumiyReport(Request $request)
 * {
 * ini_set('max_execution_time', 600);
 * ini_set('memory_limit', '1024M');
 *
 * try {
 * $validated = $request->validate([
 * 'type'        => 'required|string',
 * 'region_id'   => 'nullable|integer',
 * 'district_id' => 'nullable|integer',
 * ]);
 *
 * $type        = $validated['type'];
 * $region_id   = $validated['region_id']   ?? null;
 * $district_id = $validated['district_id'] ?? null;
 *
 * if ($type === 'overall_report') {
 *
 * $where  = '';
 * $params = [];
 *
 * if (!empty($region_id)) {
 * $where  .= ' AND ab.region_id = ?';
 * $params[] = $region_id;
 * }
 * if (!empty($district_id)) {
 * $where  .= ' AND ab.district_id = ?';
 * $params[] = $district_id;
 * }
 *
 * $sql = "
 * SELECT
 * vil.name_uz AS viloyat_name,
 * tum.name_uz AS tuman_name,
 * ab.address,
 * ab.uy_raqami,
 * 1 AS jami_kop_kvartirali_uylar,
 * ab.cadastr_number,
 * ab.construction_year,
 * ap_bak.uyning_uzunligi,
 * ap_bak.uyning_eni,
 * ap_bak.uyning_balandligi,
 * ap_bak.qavatlar_balandligi->0->>'tip_qavat' AS xona_shiftining_balandilig,
 * ap_bak.qavatlar_soni,
 * COALESCE(kir_yul_sonis.count, 0) AS yulaklar_soni,
 * COALESCE(ap_lifts.count, 0)      AS liftlar_soni,
 * COALESCE((
 * SELECT SUM(count::numeric)
 * FROM apartment_base_xonadonlars
 * WHERE apartment_base_id = ab.id
 * AND deleted_at IS NULL
 * ), 0) AS xonadonlar_soni,
 * ap_bak.uy_umumiy_maydoni->>'kv' AS uy_maydoni,
 * ap_bak.xonadonning_umumiy_maydoni,
 * ap_bak.noturar_obyektlar_soni,
 * kq.label AS dom_boshqaruv_usuli_turi,
 * bt.label AS boshqaruv_usuli_tanlanmagan,
 * td.nomi      AS tashqi_devor_turi,
 * fi_qism.nomi AS fasad_issiqlik_qoplama_turi,
 * (
 * SELECT STRING_AGG(DISTINCT fbm.nomi, ', ')
 * FROM fasad_bezak_materiallaris fbm
 * WHERE fbm.code IN (
 * SELECT (elem)::int
 * FROM jsonb_array_elements_text(af.fasad_bezak_materialllari::jsonb) elem
 * )
 * ) AS fasad_bezak_materiallari,
 * af.fasad_umumiy_maydoni,
 * (
 * SELECT STRING_AGG(DISTINCT tt.nomi, ', ')
 * FROM tom_turis tt
 * WHERE tt.code IN (
 * SELECT (elem)::int
 * FROM jsonb_array_elements_text(at_q.tomning_turi::jsonb) elem
 * )
 * ) AS tomning_turi,
 * at_q.tomning_umumiy_maydoni,
 * tq.nomi AS tomning_himoya_qoplamalari,
 * amt.issiqlik_tizimi_qurilmasi_turi,
 * amt.termal_tugunlar_soni,
 * it.label AS ichki_isitish_tizimi_turi,
 * wnd.deraza_yogoch_dona,  wnd.deraza_yogoch_eni,  wnd.deraza_yogoch_boyi,  wnd.deraza_yogoch_kvm,
 * wnd.deraza_alyum_dona,   wnd.deraza_alyum_eni,   wnd.deraza_alyum_boyi,   wnd.deraza_alyum_kvm,
 * wnd.deraza_plastik_dona, wnd.deraza_plastik_eni, wnd.deraza_plastik_boyi, wnd.deraza_plastik_kvm,
 * xow.xon_oyna_yogoch_dona,  xow.xon_oyna_yogoch_eni,  xow.xon_oyna_yogoch_boyi,  xow.xon_oyna_yogoch_kvm,
 * xow.xon_oyna_alyum_dona,   xow.xon_oyna_alyum_eni,   xow.xon_oyna_alyum_boyi,   xow.xon_oyna_alyum_kvm,
 * xow.xon_oyna_plastik_dona, xow.xon_oyna_plastik_eni, xow.xon_oyna_plastik_boyi, xow.xon_oyna_plastik_kvm,
 * door.eshik_yogoch_dona, door.eshik_yogoch_eni, door.eshik_yogoch_boyi, door.eshik_yogoch_kvm,
 * door.eshik_polat_dona,  door.eshik_polat_eni,  door.eshik_polat_boyi,  door.eshik_polat_kvm
 *
 * FROM apartment_bases AS ab
 * JOIN countries AS vil ON vil.country_bill_id = ab.region_id
 * JOIN regions AS tum ON tum.id = ab.district_id
 * LEFT JOIN apartment_bino_asosiy_kursatgiches AS ap_bak
 * ON ap_bak.apartment_base_id = ab.id
 * LEFT JOIN (
 * SELECT apartment_base_id, COUNT(*) AS count
 * FROM apartment_base_uy_kirish_yulaklar_sonis
 * WHERE deleted_at IS NULL
 * GROUP BY apartment_base_id
 * ) kir_yul_sonis ON kir_yul_sonis.apartment_base_id = ab.id
 * LEFT JOIN (
 * SELECT apartment_base_id, COUNT(*) AS count
 * FROM apartment_base_lifts
 * WHERE deleted_at IS NULL
 * GROUP BY apartment_base_id
 * ) ap_lifts ON ap_lifts.apartment_base_id = ab.id
 * LEFT JOIN (
 * SELECT (e->>'value')::int AS value, (e->>'label') AS label
 * FROM utils u,
 * jsonb_array_elements(u.content->'kup_qavatli_uy_boshqaruv_usuli') e
 * ) kq ON kq.value = ap_bak.boshqaruv_usuli
 * LEFT JOIN (
 * SELECT (e->>'value')::int AS value, (e->>'label') AS label
 * FROM utils u,
 * jsonb_array_elements(u.content->'boshqaruv_usuli_tanlanmagan') e
 * ) bt ON bt.value = ap_bak.boshqaruv_usuli
 * LEFT JOIN apartment_bino_konstruktiv_qismis AS aqk
 * ON aqk.apartment_base_id = ab.id
 * LEFT JOIN tashqi_devor_turis AS td
 * ON td.code = aqk.tashqi_devor_turi
 * LEFT JOIN aparment_fasad_qismis AS af
 * ON af.aparment_base_id = ab.id
 * LEFT JOIN fasad_qismi_issiqlik_himoya_qoplamasi_mavjudligis AS fi_qism
 * ON fi_qism.code = af.fasad_qismi_himoya_material_mavjudligi
 * LEFT JOIN apartment_tom_qismis at_q
 * ON at_q.apartment_base_id = ab.id
 * LEFT JOIN tom_himoya_qoplamalaris AS tq
 * ON tq.code = at_q.tomning_himoya_qoplamalari
 * LEFT JOIN apartment_muhandislik_texnik_taminot_tizimlars amt
 * ON amt.apartment_base_id = ab.id
 * LEFT JOIN (
 * SELECT (e->>'value')::int AS value, (e->>'label') AS label
 * FROM utils u,
 * jsonb_array_elements(u.content->'markaziy_issiqlik_tizimi') e
 * ) it ON it.value = amt.ichki_isitish_tizimi_turi
 * LEFT JOIN (
 * SELECT
 * abkyd.apartment_base_id,
 * SUM(CASE WHEN abkyd.nomi = '1' THEN NULLIF(abkyd.dona,'')::numeric ELSE 0::numeric END) AS deraza_yogoch_dona,
 * MAX(CASE WHEN abkyd.nomi = '1' THEN NULLIF(abkyd.eni, '')::numeric ELSE NULL END)       AS deraza_yogoch_eni,
 * MAX(CASE WHEN abkyd.nomi = '1' THEN NULLIF(abkyd.buyi,'')::numeric ELSE NULL END)       AS deraza_yogoch_boyi,
 * SUM(CASE WHEN abkyd.nomi = '1' THEN NULLIF(abkyd.kv,  '')::numeric ELSE 0::numeric END) AS deraza_yogoch_kvm,
 * SUM(CASE WHEN abkyd.nomi = '2' THEN NULLIF(abkyd.dona,'')::numeric ELSE 0::numeric END) AS deraza_alyum_dona,
 * MAX(CASE WHEN abkyd.nomi = '2' THEN NULLIF(abkyd.eni, '')::numeric ELSE NULL END)       AS deraza_alyum_eni,
 * MAX(CASE WHEN abkyd.nomi = '2' THEN NULLIF(abkyd.buyi,'')::numeric ELSE NULL END)       AS deraza_alyum_boyi,
 * SUM(CASE WHEN abkyd.nomi = '2' THEN NULLIF(abkyd.kv,  '')::numeric ELSE 0::numeric END) AS deraza_alyum_kvm,
 * SUM(CASE WHEN abkyd.nomi = '3' THEN NULLIF(abkyd.dona,'')::numeric ELSE 0::numeric END) AS deraza_plastik_dona,
 * MAX(CASE WHEN abkyd.nomi = '3' THEN NULLIF(abkyd.eni, '')::numeric ELSE NULL END)       AS deraza_plastik_eni,
 * MAX(CASE WHEN abkyd.nomi = '3' THEN NULLIF(abkyd.buyi,'')::numeric ELSE NULL END)       AS deraza_plastik_boyi,
 * SUM(CASE WHEN abkyd.nomi = '3' THEN NULLIF(abkyd.kv,  '')::numeric ELSE 0::numeric END) AS deraza_plastik_kvm
 * FROM apartment_base_kirish_yulak_derazalars abkyd
 * GROUP BY abkyd.apartment_base_id
 * ) wnd ON wnd.apartment_base_id = ab.id
 * LEFT JOIN (
 * SELECT
 * xo.apartment_base_id,
 * SUM(CASE WHEN xo.nomi = '1' THEN NULLIF(xo.dona,'')::numeric ELSE 0::numeric END) AS xon_oyna_yogoch_dona,
 * MAX(CASE WHEN xo.nomi = '1' THEN NULLIF(xo.eni, '')::numeric ELSE NULL END)       AS xon_oyna_yogoch_eni,
 * MAX(CASE WHEN xo.nomi = '1' THEN NULLIF(xo.buyi,'')::numeric ELSE NULL END)       AS xon_oyna_yogoch_boyi,
 * SUM(CASE WHEN xo.nomi = '1' THEN NULLIF(xo.kv,  '')::numeric ELSE 0::numeric END) AS xon_oyna_yogoch_kvm,
 * SUM(CASE WHEN xo.nomi = '2' THEN NULLIF(xo.dona,'')::numeric ELSE 0::numeric END) AS xon_oyna_alyum_dona,
 * MAX(CASE WHEN xo.nomi = '2' THEN NULLIF(xo.eni, '')::numeric ELSE NULL END)       AS xon_oyna_alyum_eni,
 * MAX(CASE WHEN xo.nomi = '2' THEN NULLIF(xo.buyi,'')::numeric ELSE NULL END)       AS xon_oyna_alyum_boyi,
 * SUM(CASE WHEN xo.nomi = '2' THEN NULLIF(xo.kv,  '')::numeric ELSE 0::numeric END) AS xon_oyna_alyum_kvm,
 * SUM(CASE WHEN xo.nomi = '3' THEN NULLIF(xo.dona,'')::numeric ELSE 0::numeric END) AS xon_oyna_plastik_dona,
 * MAX(CASE WHEN xo.nomi = '3' THEN NULLIF(xo.eni, '')::numeric ELSE NULL END)       AS xon_oyna_plastik_eni,
 * MAX(CASE WHEN xo.nomi = '3' THEN NULLIF(xo.buyi,'')::numeric ELSE NULL END)       AS xon_oyna_plastik_boyi,
 * SUM(CASE WHEN xo.nomi = '3' THEN NULLIF(xo.kv,  '')::numeric ELSE 0::numeric END) AS xon_oyna_plastik_kvm
 * FROM apartment_base_xonadon_oynalar_sonis xo
 * GROUP BY xo.apartment_base_id
 * ) xow ON xow.apartment_base_id = ab.id
 * LEFT JOIN (
 * SELECT
 * d.apartment_base_id,
 * SUM(NULLIF(TRIM(d.dona::text), '')::numeric) FILTER (WHERE d.nomi = '1') AS eshik_yogoch_dona,
 * MAX(NULLIF(TRIM(d.eni::text),  '')::numeric) FILTER (WHERE d.nomi = '1') AS eshik_yogoch_eni,
 * MAX(NULLIF(TRIM(d.buyi::text), '')::numeric) FILTER (WHERE d.nomi = '1') AS eshik_yogoch_boyi,
 * SUM(NULLIF(TRIM(d.kv::text),   '')::numeric) FILTER (WHERE d.nomi = '1') AS eshik_yogoch_kvm,
 * SUM(NULLIF(TRIM(d.dona::text), '')::numeric) FILTER (WHERE d.nomi = '2') AS eshik_polat_dona,
 * MAX(NULLIF(TRIM(d.eni::text),  '')::numeric) FILTER (WHERE d.nomi = '2') AS eshik_polat_eni,
 * MAX(NULLIF(TRIM(d.buyi::text), '')::numeric) FILTER (WHERE d.nomi = '2') AS eshik_polat_boyi,
 * SUM(NULLIF(TRIM(d.kv::text),   '')::numeric) FILTER (WHERE d.nomi = '2') AS eshik_polat_kvm
 * FROM apartment_base_kirish_yuli_eshiklars d
 * GROUP BY d.apartment_base_id
 * ) door ON door.apartment_base_id = ab.id
 *
 * WHERE ab.cadastr_number IS NOT NULL
 * $where
 * ORDER BY ab.id
 * ";
 *
 * $results = collect(DB::select($sql, $params));
 *
 * } /*elseif ($type === 'steps_report_by_country') {
 *
 * $sql = "
 * SELECT
 * c.name_uz                                                              AS tuman_shahar,
 * COUNT(*) FILTER (WHERE ab.step = 1 AND ab.status = 0)                 AS birinchi_qadam,
 * COUNT(*) FILTER (WHERE ab.step = 2 AND ab.status = 0)                 AS ikkinchi_qadam,
 * COUNT(*) FILTER (WHERE ab.step = 3 AND ab.status = 0)                 AS uchinchi_qadam,
 * COUNT(*) FILTER (WHERE ab.status = 1)                                  AS maqullashga_jonatildi,
 * COUNT(*) FILTER (WHERE ab.status = 2)                                  AS maqullashdan_qaytdi
 * FROM apartment_bases ab
 * LEFT JOIN apartment_stepone_section_completions assc ON assc.apartment_id = ab.id
 * LEFT JOIN countries c                                ON c.country_bill_id  = ab.region_id
 * LEFT JOIN regions r                                  ON r.id               = ab.district_id
 * WHERE (
 * assc.is_umumiy_malumotlar_completed    IS NOT NULL OR
 * assc.is_asosiy_korsatgichlar_completed IS NOT NULL OR
 * assc.is_konstruktiv_qismi_completed    IS NOT NULL OR
 * assc.is_fasad_qismi_completed          IS NOT NULL OR
 * assc.is_tom_qismi_completed            IS NOT NULL OR
 * assc.is_issiqlik_completed             IS NOT NULL OR
 * assc.is_sovuqsuv_completed             IS NOT NULL OR
 * assc.is_issiqsuv_completed             IS NOT NULL OR
 * assc.is_oqova_completed                IS NOT NULL OR
 * assc.is_gaz_taminoti_completed         IS NOT NULL OR
 * assc.is_elektr_taminoti_completed      IS NOT NULL
 * )
 * GROUP BY c.name_uz
 * ";
 *
 * $results = collect(DB::select($sql));
 *
 * } else {
 *
 * $sql = "
 * SELECT
 * r.name_uz                                                              AS tuman_shahar,
 * COUNT(*) FILTER (WHERE ab.step = 1 AND ab.status = 0)                 AS birinchi_qadam,
 * COUNT(*) FILTER (WHERE ab.step = 2 AND ab.status = 0)                 AS ikkinchi_qadam,
 * COUNT(*) FILTER (WHERE ab.step = 3 AND ab.status = 0)                 AS uchinchi_qadam,
 * COUNT(*) FILTER (WHERE ab.status = 1)                                  AS maqullashga_jonatildi,
 * COUNT(*) FILTER (WHERE ab.status = 2)                                  AS maqullashdan_qaytdi
 * FROM apartment_bases ab
 * LEFT JOIN apartment_stepone_section_completions assc ON assc.apartment_id = ab.id
 * LEFT JOIN countries c                                ON c.country_bill_id  = ab.region_id
 * LEFT JOIN regions r                                  ON r.id               = ab.district_id
 * WHERE (
 * assc.is_umumiy_malumotlar_completed    IS NOT NULL OR
 * assc.is_asosiy_korsatgichlar_completed IS NOT NULL OR
 * assc.is_konstruktiv_qismi_completed    IS NOT NULL OR
 * assc.is_fasad_qismi_completed          IS NOT NULL OR
 * assc.is_tom_qismi_completed            IS NOT NULL OR
 * assc.is_issiqlik_completed             IS NOT NULL OR
 * assc.is_sovuqsuv_completed             IS NOT NULL OR
 * assc.is_issiqsuv_completed             IS NOT NULL OR
 * assc.is_oqova_completed                IS NOT NULL OR
 * assc.is_gaz_taminoti_completed         IS NOT NULL OR
 * assc.is_elektr_taminoti_completed      IS NOT NULL
 * )
 * AND c.country_bill_id = 13
 * GROUP BY r.name_uz
 * ";
 *
 * $results = collect(DB::select($sql));
 * }
 *
 * return Excel::download(
 * new ApartmentReportExport($results, $type, $region_id),
 * 'turarjoy_passport_hisobot.xlsx'
 * );
 *
 * } catch (\Exception $e) {
    *
    Log::error('XATO', [
 * 'error' => $e->getMessage(),
 * 'line'  => $e->getLine(),
 * 'file'  => $e->getFile(),
 * ]);
 * return response()->json([
 * 'error' => $e->getMessage(),
 * 'line'  => $e->getLine(),
 * 'file'  => $e->getFile(),
 * ], 500);
 * }
 * }
 */
/*namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ApartmentReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $data;
    protected $keys = [];
    protected $region_id;
    protected $type;

    private function windowSectionsConfig()
    {
        return [
            [
                'title' => "4.5 KIRISH YO'LAKLARDAGI DERAZALAR (zina bo'linmasida)",
                'top_range' => ['deraza_yogoch_dona', 'deraza_plastik_kvm'],
                'mini' => [
                    ["Yog'och", 'deraza_yogoch_dona', 'deraza_yogoch_kvm'],
                    ['Alyuminiy', 'deraza_alyum_dona', 'deraza_alyum_kvm'],
                    ['Plastik', 'deraza_plastik_dona', 'deraza_plastik_kvm'],
                ],
                'units' => [
                    'deraza_yogoch_dona' => 'dona', 'deraza_yogoch_eni' => 'eni', 'deraza_yogoch_boyi' => "bo'yi", 'deraza_yogoch_kvm' => 'kv.m',
                    'deraza_alyum_dona' => 'dona', 'deraza_alyum_eni' => 'eni', 'deraza_alyum_boyi' => "bo'yi", 'deraza_alyum_kvm' => 'kv.m',
                    'deraza_plastik_dona' => 'dona', 'deraza_plastik_eni' => 'eni', 'deraza_plastik_boyi' => "bo'yi", 'deraza_plastik_kvm' => 'kv.m',
                ],
            ],
            [
                'title' => "4.6 XONADON OYNALARI SONI",
                'top_range' => ['xon_oyna_yogoch_dona', 'xon_oyna_plastik_kvm'],
                'mini' => [
                    ["Yog'och", 'xon_oyna_yogoch_dona', 'xon_oyna_yogoch_kvm'],
                    ['Alyuminiy', 'xon_oyna_alyum_dona', 'xon_oyna_alyum_kvm'],
                    ['Plastik', 'xon_oyna_plastik_dona', 'xon_oyna_plastik_kvm'],
                ],
                'units' => [
                    'xon_oyna_yogoch_dona' => 'dona', 'xon_oyna_yogoch_eni' => 'eni', 'xon_oyna_yogoch_boyi' => "bo'yi", 'xon_oyna_yogoch_kvm' => 'kv.m',
                    'xon_oyna_alyum_dona' => 'dona', 'xon_oyna_alyum_eni' => 'eni', 'xon_oyna_alyum_boyi' => "bo'yi", 'xon_oyna_alyum_kvm' => 'kv.m',
                    'xon_oyna_plastik_dona' => 'dona', 'xon_oyna_plastik_eni' => 'eni', 'xon_oyna_plastik_boyi' => "bo'yi", 'xon_oyna_plastik_kvm' => 'kv.m',
                ],
            ],
            [
                'title' => "4.7 KIRISH YO'LAKLARDAGI ESHIKLAR",
                'top_range' => ['eshik_yogoch_dona', 'eshik_polat_kvm'],
                'mini' => [
                    ["Yog'och", 'eshik_yogoch_dona', 'eshik_yogoch_kvm'],
                    ["Po'lat", 'eshik_polat_dona', 'eshik_polat_kvm'],
                ],
                'units' => [
                    'eshik_yogoch_dona' => 'dona', 'eshik_yogoch_eni' => 'eni', 'eshik_yogoch_boyi' => "bo'yi", 'eshik_yogoch_kvm' => 'kv.m',
                    'eshik_polat_dona' => 'dona', 'eshik_polat_eni' => 'eni', 'eshik_polat_boyi' => "bo'yi", 'eshik_polat_kvm' => 'kv.m',
                ],
            ],
        ];
    }

    public function __construct(Collection $data, string $type, ?int $region_id = null)
    {
        $this->data = $data;
        $this->type = $type;
        $this->region_id = $region_id;

        if ($this->type === 'overall_report') {
            $this->keys = [
                'viloyat_name',
                'tuman_name',
                'address',
                'uy_raqami',
                'jami_kop_kvartirali_uylar',
                'cadastr_number',
                'construction_year',
                'uyning_uzunligi',
                'uyning_eni',
                'uyning_balandligi',
                'xona_shiftining_balandilig',
                'qavatlar_soni',
                'yulaklar_soni',
                'liftlar_soni',
                'xonadonlar_soni',
                'uy_maydoni',
                'xonadonning_umumiy_maydoni',
                'noturar_obyektlar_soni',
                'dom_boshqaruv_usuli_turi',
                'boshqaruv_usuli_tanlanmagan',
                'tashqi_devor_turi',
                'fasad_issiqlik_qoplama_turi',
                'fasad_bezak_materiallari',
                'fasad_umumiy_maydoni',
                'tomning_turi',
                'tomning_umumiy_maydoni',
                'tomning_himoya_qoplamalari',
            ];

            $sectionLeafKeys = [];
            foreach ($this->windowSectionsConfig() as $sec) {
                foreach ($sec['mini'] as $m) {
                    $first = $m[1];
                    $prefix = substr($first, 0, strrpos($first, '_'));
                    $sectionLeafKeys = array_merge($sectionLeafKeys, [
                        $prefix . '_dona',
                        $prefix . '_eni',
                        $prefix . '_boyi',
                        $prefix . '_kvm',
                    ]);
                }
            }

            $sectionLeafKeys = array_values(array_unique($sectionLeafKeys));

            $postKeys = [
                'ichki_isitish_tizimi_turi',
                'issiqlik_tizimi_qurilmasi_turi',
                'termal_tugunlar_soni',
            ];

            $this->keys = array_merge($this->keys, $sectionLeafKeys, $postKeys);

        } elseif ($this->type === 'steps_report_by_country') {
            $this->keys = [
                'tuman_shahar', 'birinchi_qadam', 'ikkinchi_qadam',
                'uchinchi_qadam', 'maqullashga_jonatildi', 'maqullashdan_qaytdi',
            ];
        } else {
            $this->keys = [
                'tuman_shahar', 'birinchi_qadam', 'ikkinchi_qadam',
                'uchinchi_qadam', 'maqullashga_jonatildi', 'maqullashdan_qaytdi',
            ];
        }
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        $baseHeadings = [
            "Hudud nomi",
            "Tuman",
            "Mahalla ko'cha",
            "Uy raqami",
            "Jami ko'p kvartirali uylar",
            "Kadastr raqami",
            "Qurilgan yili",
            "Uyning uzunligi",
            "Uyning eni",
            "Uyning balandligi",
            "Xona-shiftining balandligi",
            "Qavatlar soni",
            "Kirish yo'laklari soni",
            "Liftlar soni",
            "Xonadonlar soni",
            "Umumiy maydoni",
            "Xonadonning umumiy maydoni",
            "Noturar obyektlar soni",
            "Boshqaruv usuli",
            "Boshqaruv usuli tanlanmagan",
            "Tashqi devor turi",
            "Fasad issiqlik qoplamasi",
            "Fasad bezak materiallari",
            "Fasad umumiy maydoni",
            "Tomning turi",
            "Tomning umumiy maydoni",
            "Tomning himoya qoplamlari",
        ];

        $leafs = [];
        foreach ($this->windowSectionsConfig() as $sec) {
            $leafs = array_merge($leafs, array_fill(0, count($sec['mini']) * 4, ''));
        }

        $afterHeadings = [
            "Ichki isitish tizimi turi",
            "Issiqlik tizimi turi",
            "Termal tugunlar soni",
        ];

        $step_by_country = [
            "Hudud (viloyat)", "1-qadamdagi obyektlar soni", "2-qadamdagi obyektlar soni",
            "3-qadamdagi obyektlar soni", "Maqullashga yuborilgan", "Maqullashdan qaytgan",
        ];

        $step_by_region = [
            "Tuman/Shahar", "1-qadamdagi obyektlar soni", "2-qadamdagi obyektlar soni",
            "3-qadamdagi obyektlar soni", "Maqullashga yuborilgan", "Maqullashdan qaytgan",
        ];

        if ($this->type === 'overall_report') {
            return array_merge($baseHeadings, $leafs, $afterHeadings);
        } elseif ($this->type === 'steps_report_by_country') {
            return $step_by_country;
        } else {
            return $step_by_region;
        }
    }

    public function map($row): array
    {
        $r = (array)$row;
        $hasCadastr = !empty($r['cadastr_number']);

        return array_map(function ($k) use ($r, $hasCadastr) {
            if ($k === 'jami_kop_kvartirali_uylar') {
                return $hasCadastr ? 1 : '-';
            }
            $v = $r[$k] ?? null;
            return (is_array($v) || is_object($v))
                ? json_encode($v, JSON_UNESCAPED_UNICODE)
                : $v;
        }, $this->keys);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $e) {
                $s = $e->sheet->getDelegate();
                $s->insertNewRowBefore(1, 1);
                $s->insertNewRowBefore(3, 1);

                $lastCol = $s->getHighestDataColumn();
                $lastRow = $s->getHighestDataRow();

                $col = function ($i) {
                    return Coordinate::stringFromColumnIndex($i);
                };

                $idx = [];
                foreach ($this->keys as $i => $k) {
                    $idx[$k] = $i + 1;
                }

                $baseGroups = [
                    ["1.Ko'p xonadonli uyning umumiy ma'lumotlari", 'viloyat_name', 'construction_year',],
                    ["2.Binoning asosiy ko'rsatgichlari", 'uyning_uzunligi', 'noturar_obyektlar_soni',],
                    ["3.Ko'p qavatli uyning boshqaruv usuli", 'dom_boshqaruv_usuli_turi', 'boshqaruv_usuli_tanlanmagan',],
                    ['4.2 Fasad qismi', 'tashqi_devor_turi', 'fasad_umumiy_maydoni',],
                    ['4.3 Tom qismi', 'tomning_turi', 'tomning_himoya_qoplamalari',],
                    ['5. Issiqlik tizimi kirish tuguni', 'ichki_isitish_tizimi_turi', 'termal_tugunlar_soni',],
                ];

                $applyTop = function ($c1, $c2, $title) use ($s) {
                    $s->mergeCells("{$c1}1:{$c2}1");
                    $s->setCellValue("{$c1}1", $title);
                    $s->getStyle("{$c1}1:{$c2}1")->applyFromArray([
                        'font' => ['bold' => true],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                };

                foreach ($baseGroups as $g) {
                    list($title, $first, $last) = $g;
                    if (!isset($idx[$first], $idx[$last])) continue;
                    $applyTop($col($idx[$first]), $col($idx[$last]), $title);
                }

                foreach ($this->windowSectionsConfig() as $sec) {
                    list($firstKey, $lastKey) = $sec['top_range'];
                    if (isset($idx[$firstKey], $idx[$lastKey])) {
                        $applyTop($col($idx[$firstKey]), $col($idx[$lastKey]), $sec['title']);
                    }

                    foreach ($sec['mini'] as $m) {
                        $fk = $m[1];
                        $lk = $m[2];
                        if (!isset($idx[$fk], $idx[$lk])) continue;
                        $c1 = $col($idx[$fk]);
                        $c2 = $col($idx[$lk]);
                        $s->mergeCells("{$c1}2:{$c2}2");
                        $s->setCellValue("{$c1}2", $m[0]);
                        $s->getStyle("{$c1}2:{$c2}2")->applyFromArray([
                            'font' => ['bold' => true],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        ]);
                    }
                }

                $lastCol = $col(count($this->keys));

                $s->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                $units = [
                    'uyning_uzunligi' => 'm',
                    'uyning_eni' => 'm',
                    'uyning_balandligi' => 'm',
                    'xona_shiftining_balandilig' => 'm',
                    'qavatlar_soni' => 'dona',
                    'yulaklar_soni' => 'dona',
                    'liftlar_soni' => 'dona',
                    'xonadonning_umumiy_maydoni' => 'kv.m',
                    'fasad_umumiy_maydoni' => 'kv.m',
                    'tomning_umumiy_maydoni' => 'kv.m',
                    'termal_tugunlar_soni' => 'dona',
                    'noturar_obyektlar_soni' => 'dona',
                    'uy_maydoni' => 'kv.m',
                ];

                foreach ($this->windowSectionsConfig() as $sec) {
                    $units = array_merge($units, $sec['units']);
                }

                foreach ($this->keys as $i => $k) {
                    $s->setCellValue($col($i + 1) . '3', isset($units[$k]) ? $units[$k] : '');
                }

                $s->getStyle("A3:{$lastCol}3")->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                $s->getRowDimension(1)->setRowHeight(24);
                $s->getRowDimension(2)->setRowHeight(22);
                $s->getRowDimension(3)->setRowHeight(18);
                $s->freezePane('A4');
                $s->getDefaultColumnDimension()->setWidth(18);
                $s->getColumnDimension('A')->setWidth(24);

                $range = "A1:{$lastCol}{$lastRow}";
                $s->getStyle($range)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $s->getStyle($range)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('000000');
            },
        ];
    }
}*/

/*namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ApartmentReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $data;
    protected $keys = [];
    protected $region_id;
    protected $type;

    private function windowSectionsConfig()
    {
        return [
            [
                'title'     => "4.5 KIRISH YO'LAKLARDAGI DERAZALAR (zina bo'linmasida)",
                'top_range' => ['deraza_yogoch_dona', 'deraza_plastik_kvm'],
                'mini'      => [
                    ["Yog'och",  'deraza_yogoch_dona',  'deraza_yogoch_kvm'],
                    ['Alyuminiy','deraza_alyum_dona',   'deraza_alyum_kvm'],
                    ['Plastik',  'deraza_plastik_dona', 'deraza_plastik_kvm'],
                ],
                'units' => [
                    'deraza_yogoch_dona'  => 'dona', 'deraza_yogoch_eni'  => 'eni', 'deraza_yogoch_boyi'  => "bo'yi", 'deraza_yogoch_kvm'  => 'kv.m',
                    'deraza_alyum_dona'   => 'dona', 'deraza_alyum_eni'   => 'eni', 'deraza_alyum_boyi'   => "bo'yi", 'deraza_alyum_kvm'   => 'kv.m',
                    'deraza_plastik_dona' => 'dona', 'deraza_plastik_eni' => 'eni', 'deraza_plastik_boyi' => "bo'yi", 'deraza_plastik_kvm' => 'kv.m',
                ],
            ],
            [
                'title'     => "4.6 XONADON OYNALARI SONI",
                'top_range' => ['xon_oyna_yogoch_dona', 'xon_oyna_plastik_kvm'],
                'mini'      => [
                    ["Yog'och",  'xon_oyna_yogoch_dona',  'xon_oyna_yogoch_kvm'],
                    ['Alyuminiy','xon_oyna_alyum_dona',   'xon_oyna_alyum_kvm'],
                    ['Plastik',  'xon_oyna_plastik_dona', 'xon_oyna_plastik_kvm'],
                ],
                'units' => [
                    'xon_oyna_yogoch_dona'  => 'dona', 'xon_oyna_yogoch_eni'  => 'eni', 'xon_oyna_yogoch_boyi'  => "bo'yi", 'xon_oyna_yogoch_kvm'  => 'kv.m',
                    'xon_oyna_alyum_dona'   => 'dona', 'xon_oyna_alyum_eni'   => 'eni', 'xon_oyna_alyum_boyi'   => "bo'yi", 'xon_oyna_alyum_kvm'   => 'kv.m',
                    'xon_oyna_plastik_dona' => 'dona', 'xon_oyna_plastik_eni' => 'eni', 'xon_oyna_plastik_boyi' => "bo'yi", 'xon_oyna_plastik_kvm' => 'kv.m',
                ],
            ],
            [
                'title'     => "4.7 KIRISH YO'LAKLARDAGI ESHIKLAR",
                'top_range' => ['eshik_yogoch_dona', 'eshik_polat_kvm'],
                'mini'      => [
                    ["Yog'och", 'eshik_yogoch_dona', 'eshik_yogoch_kvm'],
                    ["Po'lat",  'eshik_polat_dona',  'eshik_polat_kvm'],
                ],
                'units' => [
                    'eshik_yogoch_dona' => 'dona', 'eshik_yogoch_eni' => 'eni', 'eshik_yogoch_boyi' => "bo'yi", 'eshik_yogoch_kvm' => 'kv.m',
                    'eshik_polat_dona'  => 'dona', 'eshik_polat_eni'  => 'eni', 'eshik_polat_boyi'  => "bo'yi", 'eshik_polat_kvm'  => 'kv.m',
                ],
            ],
        ];
    }

    public function __construct(Collection $data, string $type, ?int $region_id = null)
    {
        $this->data      = $data;
        $this->type      = $type;
        $this->region_id = $region_id;

        if ($this->type === 'overall_report') {
            $this->keys = [
                'viloyat',
                'tuman',
                'mahalla_kocha',
                'uy_raqami',
                'jami_kop_kvartirali_uylar',
                'cadastr_number',
                'qurilgan_yili',
                'uyning_uzunligi',
                'uyning_eni',
                'uyning_balandligi',
                'xona_shiftining_balandilig',
                'qavatlar_soni',
                'yolaklar_soni',
                'liftlar_soni',
                'xonadonlar_soni',
                'uy_maydoni',
                'xonadonning_umumiy_maydoni',
                'noturar_obyektlar_soni',
                'dom_boshqaruv_usuli_turi',
                'boshqaruv_usuli_tanlanmagan',
                'tashqi_devor_turi',
                'fasad_issiqlik_qoplama_turi',
                'fasad_bezak_materiallari',
                'fasad_umumiy_maydoni',
                'tomning_turi',
                'tomning_umumiy_maydoni',
                'tomning_himoya_qoplamalari',
            ];

            $sectionLeafKeys = [];
            foreach ($this->windowSectionsConfig() as $sec) {
                foreach ($sec['mini'] as $m) {
                    $first  = $m[1];
                    $last   = $m[2];
                    $prefix = substr($first, 0, strrpos($first, '_'));
                    $sectionLeafKeys = array_merge($sectionLeafKeys, [
                        $prefix . '_dona', $prefix . '_eni', $prefix . '_boyi', $prefix . '_kvm'
                    ]);
                }
            }

            $sectionLeafKeys = array_values(array_unique($sectionLeafKeys));
            $postKeys        = ['ichki_isitish_tizimi_turi', 'issiqlik_tizimi_qurilmasi_turi', 'termal_tugunlar_soni'];
            $this->keys      = array_merge($this->keys, $sectionLeafKeys, $postKeys);

        } elseif ($this->type === 'steps_report_by_country') {
            $this->keys = [
                'tuman_shahar', 'birinchi_qadam', 'ikkinchi_qadam',
                'uchinchi_qadam', 'maqullashga_jonatildi', 'maqullashdan_qaytdi',
            ];
        } else {
            $this->keys = [
                'tuman_shahar', 'birinchi_qadam', 'ikkinchi_qadam',
                'uchinchi_qadam', 'maqullashga_jonatildi', 'maqullashdan_qaytdi',
            ];
        }
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        $baseHeadings = [
            "Hudud nomi",
            "Tuman",
            "Mahalla ko'cha",
            "Uy raqami",
            "Jami ko'p kvartirali uylar",
            "Kadastr raqami",
            "Qurilgan yili",
            "Uyning uzunligi",
            "Uyning eni",
            "Uyning balandligi",
            "Xona-shiftining balandligi",
            "Qavatlar soni",
            "Ko'p xonadonli uyning kirish yo'laklari soni",
            "Mavjud liftlar soni",
            "Ko'p xonadonli uyning Xonadonlar soni",
            "Ko'p xonadonli uyning umumiy maydoni",
            "Xonadonning umumiy maydoni",
            "Noturar obyektlar soni",
            "Ko'p qavatli uyning boshqaruv usuli",
            "Boshqaruv usuli tanlanmagan",
            "Tashqi devor turi",
            "Fasad qismida issiqlik himoya qoplamasi material mavjudligi (turi).",
            "Fasad bezak materiallari",
            "Fasad umumiy maydoni",
            "Tomning turi(balandligi)",
            "Tomning umumiy maydoni",
            "Tomning himoya qoplamlari(tarkibi)",
        ];

        $leafs = [];
        foreach ($this->windowSectionsConfig() as $sec) {
            $leafs = array_merge($leafs, array_fill(0, count($sec['mini']) * 4, ''));
        }

        $afterHeadings = [
            "Ichki isitish tizimi turi",
            "Issiqlik tizimi turi",
            "Termal tugunlar soni",
        ];

        $step_by_country = [
            "Hudud (viloyat)",
            "1-qadamdagi obyektlar soni",
            "2-qadamdagi obyektlar soni",
            "3-qadamdagi obyektlar soni",
            "Maqullashga yuborilgan",
            "Maqullashdan qaytgan",
        ];

        $step_by_region = [
            "Tuman/Shahar",
            "1-qadamdagi obyektlar soni",
            "2-qadamdagi obyektlar soni",
            "3-qadamdagi obyektlar soni",
            "Maqullashga yuborilgan",
            "Maqullashdan qaytgan",
        ];

        if ($this->type === 'overall_report') {
            return array_merge($baseHeadings, $leafs, $afterHeadings);
        } elseif ($this->type === 'steps_report_by_country') {
            return $step_by_country;
        } else {
            return $step_by_region;
        }
    }

    public function map($row): array
    {
        $r          = (array) $row;
        $hasCadastr = !empty($r['cadastr_number']);
        return array_map(function ($k) use ($r, $hasCadastr) {
            if ($k === 'jami_kop_kvartirali_uylar' && $k != 'cadastr_number') {
                return $hasCadastr ? 1 : '-';
            }
            $v = $r[$k] ?? null;
            return (is_array($v) || is_object($v)) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
        }, $this->keys);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('2')->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
        ]);
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $e) {
                $s = $e->sheet->getDelegate();
                $s->insertNewRowBefore(1, 1);
                $s->insertNewRowBefore(3, 1);

                $lastCol = $s->getHighestDataColumn();
                $lastRow = $s->getHighestDataRow();

                $col = function ($i) {
                    return Coordinate::stringFromColumnIndex($i);
                };

                $idx = [];
                foreach ($this->keys as $i => $k) {
                    $idx[$k] = $i + 1;
                }

                $baseGroups = [
                    ["1.Ko'p xonadonli uyning umumiy ma'lumotlari", 'viloyat',                 'qurilgan_yili'               ],
                    ["2.Binoning asosiy ko'rsatgichlari",            'uyning_uzunligi',          'noturar_obyektlar_soni'      ],
                    ["3.Ko'p qavatli uyning boshqaruv usuli",        'dom_boshqaruv_usuli_turi', 'boshqaruv_usuli_tanlanmagan'],
                    ['4.2 Fasad qismi',                              'tashqi_devor_turi',        'fasad_umumiy_maydoni'        ],
                    ['4.3 Tom qismi',                                'tomning_turi',             'tomning_himoya_qoplamalari'  ],
                    ['5. Issiqlik tizimi kirish tuguni',             'ichki_isitish_tizimi_turi','termal_tugunlar_soni'        ],
                ];

                $applyTop = function ($c1, $c2, $title) use ($s) {
                    $s->mergeCells("{$c1}1:{$c2}1");
                    $s->setCellValue("{$c1}1", $title);
                    $s->getStyle("{$c1}1:{$c2}1")->applyFromArray([
                        'font'      => ['bold' => true],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                            'wrapText'   => true,
                        ],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                };

                foreach ($baseGroups as $g) {
                    list($title, $first, $last) = $g;
                    if (!isset($idx[$first], $idx[$last])) continue;
                    $applyTop($col($idx[$first]), $col($idx[$last]), $title);
                }

                foreach ($this->windowSectionsConfig() as $sec) {
                    list($firstKey, $lastKey) = $sec['top_range'];
                    if (isset($idx[$firstKey], $idx[$lastKey])) {
                        $applyTop($col($idx[$firstKey]), $col($idx[$lastKey]), $sec['title']);
                    }

                    foreach ($sec['mini'] as $m) {
                        $fk = $m[1];
                        $lk = $m[2];
                        if (!isset($idx[$fk], $idx[$lk])) continue;
                        $c1 = $col($idx[$fk]);
                        $c2 = $col($idx[$lk]);
                        $s->mergeCells("{$c1}2:{$c2}2");
                        $s->setCellValue("{$c1}2", $m[0]);
                        $s->getStyle("{$c1}2:{$c2}2")->applyFromArray([
                            'font'      => ['bold' => true],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical'   => Alignment::VERTICAL_CENTER,
                            ],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        ]);
                    }
                }

                $lastCol = $col(count($this->keys));

                $s->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'font'      => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                $units = [
                    'uyning_uzunligi'            => 'm',
                    'uyning_eni'                 => 'm',
                    'uyning_balandligi'          => 'm',
                    'xona_shiftining_balandilig' => 'm',
                    'qavatlar_soni'              => 'dona',
                    'yolaklar_soni'              => 'dona',
                    'liftlar_soni'               => 'dona',
                    'xonadonning_umumiy_maydoni' => 'kv.m',
                    'fasad_umumiy_maydoni'       => 'kv.m',
                    'tomning_umumiy_maydoni'     => 'kv.m',
                    'termal_tugunlar_soni'       => 'dona',
                    'noturar_obyektlar_soni'     => 'dona',
                ];

                foreach ($this->windowSectionsConfig() as $sec) {
                    $units = array_merge($units, $sec['units']);
                }

                foreach ($this->keys as $i => $k) {
                    $s->setCellValue($col($i + 1) . '3', isset($units[$k]) ? $units[$k] : '');
                }

                $s->getStyle("A3:{$lastCol}3")->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                $s->getRowDimension(1)->setRowHeight(24);
                $s->getRowDimension(2)->setRowHeight(22);
                $s->getRowDimension(3)->setRowHeight(18);
                $s->freezePane('A4');
                $s->getDefaultColumnDimension()->setWidth(18);
                $s->getColumnDimension('A')->setWidth(24);

                // Header qatorlari
                $s->getStyle("A1:{$lastCol}3")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                // Barcha ma'lumot qatorlari o'rtadan
                $s->getStyle("A4:{$lastCol}{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
            },
        ];
    }
}*/
