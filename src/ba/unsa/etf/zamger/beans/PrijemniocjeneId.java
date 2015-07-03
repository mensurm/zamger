package ba.unsa.etf.zamger.beans;

// Generated May 20, 2015 3:15:09 PM by Hibernate Tools 3.4.0.CR1

/**
 * PrijemniocjeneId generated by hbm2java
 */
public class PrijemniocjeneId implements java.io.Serializable {

	private int prijemni;
	private byte razred;
	private byte ocjena;
	private byte tipocjene;

	public PrijemniocjeneId() {
	}

	public PrijemniocjeneId(int prijemni, byte razred, byte ocjena,
			byte tipocjene) {
		this.prijemni = prijemni;
		this.razred = razred;
		this.ocjena = ocjena;
		this.tipocjene = tipocjene;
	}

	public int getPrijemni() {
		return this.prijemni;
	}

	public void setPrijemni(int prijemni) {
		this.prijemni = prijemni;
	}

	public byte getRazred() {
		return this.razred;
	}

	public void setRazred(byte razred) {
		this.razred = razred;
	}

	public byte getOcjena() {
		return this.ocjena;
	}

	public void setOcjena(byte ocjena) {
		this.ocjena = ocjena;
	}

	public byte getTipocjene() {
		return this.tipocjene;
	}

	public void setTipocjene(byte tipocjene) {
		this.tipocjene = tipocjene;
	}

	public boolean equals(Object other) {
		if ((this == other))
			return true;
		if ((other == null))
			return false;
		if (!(other instanceof PrijemniocjeneId))
			return false;
		PrijemniocjeneId castOther = (PrijemniocjeneId) other;

		return (this.getPrijemni() == castOther.getPrijemni())
				&& (this.getRazred() == castOther.getRazred())
				&& (this.getOcjena() == castOther.getOcjena())
				&& (this.getTipocjene() == castOther.getTipocjene());
	}

	public int hashCode() {
		int result = 17;

		result = 37 * result + this.getPrijemni();
		result = 37 * result + this.getRazred();
		result = 37 * result + this.getOcjena();
		result = 37 * result + this.getTipocjene();
		return result;
	}

}