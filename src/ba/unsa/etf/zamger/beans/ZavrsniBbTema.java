package ba.unsa.etf.zamger.beans;

// Generated May 20, 2015 3:15:09 PM by Hibernate Tools 3.4.0.CR1

import java.util.Date;

/**
 * ZavrsniBbTema generated by hbm2java
 */
public class ZavrsniBbTema implements java.io.Serializable {

	private int id;
	private Date vrijeme;
	private int prviPost;
	private int zadnjiPost;
	private int pregleda;
	private int osoba;
	private int zavrsni;

	public ZavrsniBbTema() {
	}

	public ZavrsniBbTema(int id, Date vrijeme, int prviPost, int zadnjiPost,
			int pregleda, int osoba, int zavrsni) {
		this.id = id;
		this.vrijeme = vrijeme;
		this.prviPost = prviPost;
		this.zadnjiPost = zadnjiPost;
		this.pregleda = pregleda;
		this.osoba = osoba;
		this.zavrsni = zavrsni;
	}

	public int getId() {
		return this.id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public Date getVrijeme() {
		return this.vrijeme;
	}

	public void setVrijeme(Date vrijeme) {
		this.vrijeme = vrijeme;
	}

	public int getPrviPost() {
		return this.prviPost;
	}

	public void setPrviPost(int prviPost) {
		this.prviPost = prviPost;
	}

	public int getZadnjiPost() {
		return this.zadnjiPost;
	}

	public void setZadnjiPost(int zadnjiPost) {
		this.zadnjiPost = zadnjiPost;
	}

	public int getPregleda() {
		return this.pregleda;
	}

	public void setPregleda(int pregleda) {
		this.pregleda = pregleda;
	}

	public int getOsoba() {
		return this.osoba;
	}

	public void setOsoba(int osoba) {
		this.osoba = osoba;
	}

	public int getZavrsni() {
		return this.zavrsni;
	}

	public void setZavrsni(int zavrsni) {
		this.zavrsni = zavrsni;
	}

}