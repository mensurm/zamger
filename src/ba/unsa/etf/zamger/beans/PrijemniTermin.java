package ba.unsa.etf.zamger.beans;

// Generated May 20, 2015 3:15:09 PM by Hibernate Tools 3.4.0.CR1

import java.util.Date;

/**
 * PrijemniTermin generated by hbm2java
 */
public class PrijemniTermin implements java.io.Serializable {

	private Integer id;
	private int akademskaGodina;
	private Date datum;
	private byte ciklusStudija;

	public PrijemniTermin() {
	}

	public PrijemniTermin(int akademskaGodina, Date datum, byte ciklusStudija) {
		this.akademskaGodina = akademskaGodina;
		this.datum = datum;
		this.ciklusStudija = ciklusStudija;
	}

	public Integer getId() {
		return this.id;
	}

	public void setId(Integer id) {
		this.id = id;
	}

	public int getAkademskaGodina() {
		return this.akademskaGodina;
	}

	public void setAkademskaGodina(int akademskaGodina) {
		this.akademskaGodina = akademskaGodina;
	}

	public Date getDatum() {
		return this.datum;
	}

	public void setDatum(Date datum) {
		this.datum = datum;
	}

	public byte getCiklusStudija() {
		return this.ciklusStudija;
	}

	public void setCiklusStudija(byte ciklusStudija) {
		this.ciklusStudija = ciklusStudija;
	}

}