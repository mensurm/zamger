package ba.unsa.etf.zamger.beans;

// Generated May 20, 2015 3:15:09 PM by Hibernate Tools 3.4.0.CR1

import java.util.Date;
import java.util.HashSet;
import java.util.Set;

/**
 * AkademskaGodina generated by hbm2java
 */
public class AkademskaGodina implements java.io.Serializable {

	private int id;
	private String naziv;
	private boolean aktuelna;
	private Date pocetakZimskogSemestra;
	private Date krajZimskogSemestra;
	private Date pocetakLjetnjegSemestra;
	private Date krajLjetnjegSemestra;
	private Set<AkademskaGodinaPredmet> akademskaGodinaPredmets = new HashSet<AkademskaGodinaPredmet>(
			0);
	private Set<Ispit> ispits = new HashSet<Ispit>(0);
	private Set<Angazman> angazmans = new HashSet<Angazman>(0);
	private Set<Ponudakursa> ponudakursas = new HashSet<Ponudakursa>(0);
	private Set<KonacnaOcjena> konacnaOcjenas = new HashSet<KonacnaOcjena>(0);
	private Set<Projekat> projekats = new HashSet<Projekat>(0);
	private Set<AnketaAnketa> anketaAnketas = new HashSet<AnketaAnketa>(0);
	private Set<Kolizija> kolizijas = new HashSet<Kolizija>(0);
	private Set<NastavnikPredmet> nastavnikPredmets = new HashSet<NastavnikPredmet>(
			0);
	private Set<GgPredmet> ggPredmets = new HashSet<GgPredmet>(0);
	private Set<Kviz> kvizs = new HashSet<Kviz>(0);
	private Set<Labgrupa> labgrupas = new HashSet<Labgrupa>(0);
	private Set<AnketaPredmet> anketaPredmets = new HashSet<AnketaPredmet>(0);

	public AkademskaGodina() {
	}

	public AkademskaGodina(int id, String naziv, boolean aktuelna,
			Date pocetakZimskogSemestra, Date krajZimskogSemestra,
			Date pocetakLjetnjegSemestra, Date krajLjetnjegSemestra) {
		this.id = id;
		this.naziv = naziv;
		this.aktuelna = aktuelna;
		this.pocetakZimskogSemestra = pocetakZimskogSemestra;
		this.krajZimskogSemestra = krajZimskogSemestra;
		this.pocetakLjetnjegSemestra = pocetakLjetnjegSemestra;
		this.krajLjetnjegSemestra = krajLjetnjegSemestra;
	}

	public AkademskaGodina(int id, String naziv, boolean aktuelna,
			Date pocetakZimskogSemestra, Date krajZimskogSemestra,
			Date pocetakLjetnjegSemestra, Date krajLjetnjegSemestra,
			Set<AkademskaGodinaPredmet> akademskaGodinaPredmets,
			Set<Ispit> ispits, Set<Angazman> angazmans,
			Set<Ponudakursa> ponudakursas, Set<KonacnaOcjena> konacnaOcjenas,
			Set<Projekat> projekats, Set<AnketaAnketa> anketaAnketas,
			Set<Kolizija> kolizijas, Set<NastavnikPredmet> nastavnikPredmets,
			Set<GgPredmet> ggPredmets, Set<Kviz> kvizs,
			Set<Labgrupa> labgrupas, Set<AnketaPredmet> anketaPredmets) {
		this.id = id;
		this.naziv = naziv;
		this.aktuelna = aktuelna;
		this.pocetakZimskogSemestra = pocetakZimskogSemestra;
		this.krajZimskogSemestra = krajZimskogSemestra;
		this.pocetakLjetnjegSemestra = pocetakLjetnjegSemestra;
		this.krajLjetnjegSemestra = krajLjetnjegSemestra;
		this.akademskaGodinaPredmets = akademskaGodinaPredmets;
		this.ispits = ispits;
		this.angazmans = angazmans;
		this.ponudakursas = ponudakursas;
		this.konacnaOcjenas = konacnaOcjenas;
		this.projekats = projekats;
		this.anketaAnketas = anketaAnketas;
		this.kolizijas = kolizijas;
		this.nastavnikPredmets = nastavnikPredmets;
		this.ggPredmets = ggPredmets;
		this.kvizs = kvizs;
		this.labgrupas = labgrupas;
		this.anketaPredmets = anketaPredmets;
	}

	public int getId() {
		return this.id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public String getNaziv() {
		return this.naziv;
	}

	public void setNaziv(String naziv) {
		this.naziv = naziv;
	}

	public boolean isAktuelna() {
		return this.aktuelna;
	}

	public void setAktuelna(boolean aktuelna) {
		this.aktuelna = aktuelna;
	}

	public Date getPocetakZimskogSemestra() {
		return this.pocetakZimskogSemestra;
	}

	public void setPocetakZimskogSemestra(Date pocetakZimskogSemestra) {
		this.pocetakZimskogSemestra = pocetakZimskogSemestra;
	}

	public Date getKrajZimskogSemestra() {
		return this.krajZimskogSemestra;
	}

	public void setKrajZimskogSemestra(Date krajZimskogSemestra) {
		this.krajZimskogSemestra = krajZimskogSemestra;
	}

	public Date getPocetakLjetnjegSemestra() {
		return this.pocetakLjetnjegSemestra;
	}

	public void setPocetakLjetnjegSemestra(Date pocetakLjetnjegSemestra) {
		this.pocetakLjetnjegSemestra = pocetakLjetnjegSemestra;
	}

	public Date getKrajLjetnjegSemestra() {
		return this.krajLjetnjegSemestra;
	}

	public void setKrajLjetnjegSemestra(Date krajLjetnjegSemestra) {
		this.krajLjetnjegSemestra = krajLjetnjegSemestra;
	}

	public Set<AkademskaGodinaPredmet> getAkademskaGodinaPredmets() {
		return this.akademskaGodinaPredmets;
	}

	public void setAkademskaGodinaPredmets(
			Set<AkademskaGodinaPredmet> akademskaGodinaPredmets) {
		this.akademskaGodinaPredmets = akademskaGodinaPredmets;
	}

	public Set<Ispit> getIspits() {
		return this.ispits;
	}

	public void setIspits(Set<Ispit> ispits) {
		this.ispits = ispits;
	}

	public Set<Angazman> getAngazmans() {
		return this.angazmans;
	}

	public void setAngazmans(Set<Angazman> angazmans) {
		this.angazmans = angazmans;
	}

	public Set<Ponudakursa> getPonudakursas() {
		return this.ponudakursas;
	}

	public void setPonudakursas(Set<Ponudakursa> ponudakursas) {
		this.ponudakursas = ponudakursas;
	}

	public Set<KonacnaOcjena> getKonacnaOcjenas() {
		return this.konacnaOcjenas;
	}

	public void setKonacnaOcjenas(Set<KonacnaOcjena> konacnaOcjenas) {
		this.konacnaOcjenas = konacnaOcjenas;
	}

	public Set<Projekat> getProjekats() {
		return this.projekats;
	}

	public void setProjekats(Set<Projekat> projekats) {
		this.projekats = projekats;
	}

	public Set<AnketaAnketa> getAnketaAnketas() {
		return this.anketaAnketas;
	}

	public void setAnketaAnketas(Set<AnketaAnketa> anketaAnketas) {
		this.anketaAnketas = anketaAnketas;
	}

	public Set<Kolizija> getKolizijas() {
		return this.kolizijas;
	}

	public void setKolizijas(Set<Kolizija> kolizijas) {
		this.kolizijas = kolizijas;
	}

	public Set<NastavnikPredmet> getNastavnikPredmets() {
		return this.nastavnikPredmets;
	}

	public void setNastavnikPredmets(Set<NastavnikPredmet> nastavnikPredmets) {
		this.nastavnikPredmets = nastavnikPredmets;
	}

	public Set<GgPredmet> getGgPredmets() {
		return this.ggPredmets;
	}

	public void setGgPredmets(Set<GgPredmet> ggPredmets) {
		this.ggPredmets = ggPredmets;
	}

	public Set<Kviz> getKvizs() {
		return this.kvizs;
	}

	public void setKvizs(Set<Kviz> kvizs) {
		this.kvizs = kvizs;
	}

	public Set<Labgrupa> getLabgrupas() {
		return this.labgrupas;
	}

	public void setLabgrupas(Set<Labgrupa> labgrupas) {
		this.labgrupas = labgrupas;
	}

	public Set<AnketaPredmet> getAnketaPredmets() {
		return this.anketaPredmets;
	}

	public void setAnketaPredmets(Set<AnketaPredmet> anketaPredmets) {
		this.anketaPredmets = anketaPredmets;
	}

}